<div align="center">
  <img src="https://raw.githubusercontent.com/the-invisible-man/shorter-api/refs/heads/main/public/img/logo.webp" alt="ShortLink Logo" width="200" />
  <h1>ShortLink</h1>
</div>

# ShortLink API

![tests](https://github.com/the-invisible-man/shorter-api/actions/workflows/laravel.yml/badge.svg)
[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F61861e87-b977-4779-9a0f-0383d0c5b638%3Fdate%3D1&style=flat)](https://forge.laravel.com/servers/881138/sites/2599674)

ShortLink is a URL shortening service like TinyURL. It enables users to shorten a single URL or to upload a CSV to shorten a list of URLs. We'll go over the endpoints, system design decisions, and the corresponding flows.

## Quick Start
ShortLink has been deployed with Laravel Forge to a live site. You can find the application at  https://xhortl.ink/

You can use the CSV in: https://github.com/the-invisible-man/shorter-api/blob/main/tests/Stubs/happy.csv

**NOTE:** The frontend has been quickly put together with HTML and vanilla JS. It can be unstable, therefore you should refresh the page whenever attempting to resubmit a flow.

## How to Explore the Code
Everything lives in the `/app/Packages` directory. There are two business domains: `Url` and `Analytics`, and each of the own everything from their service classes, controllers, validation, requests, serializers, etc.

You can find the API routes at `RouteServiceProvider`.

## Overview
This API was built with two guiding principles: high availability, and very low latency. This was achieved by making deliberate decisions about everything from the technologies used, to the code architecture.

This system attempts to abide by the design-driven development, and is event driven in nature. There are two business domains in the world of ShortLink: URL, and Analytics. Both of these domains create a clear separation of business concerns, and avoid directly interacting with each other, instead, consuming events as the only means of communication. The events are currently Laravel events, but the system is designed such the domains could become two separate microservices communicating over a message queue.

| High Availability                                                                   | Low Latency                                                                 |
|-------------------------------------------------------------------------------------|-----------------------------------------------------------------------------|
| System was designed such that it can scale horizontally to adjust to traffic spikes | Aim to minimize any overhead when redirecting the user to their destination |

## API
Below, are all the endpoints that comprise the ShortLink service:

### Create Single Link
**POST** `/shorten/v1/urls`
```json
{
    "long_url": "https://www.wemod.com/"
}
```
**Response**
```json
{
    "long_url": "https://www.wemod.com/",
    "short_url:": "https://www.xhortl.ink/r/nf93os",
    "path": "nf93os"
}
```

### Create a CSV Processing Job
**POST** `/shorten/v1/urls/jobs`
```
// Form data
"file": (binary)
```
**Response**
```json
{
    "id": "42dd303d-b42f-46ab-9be2-63b72c85e3f0",
    "status:": "pending"
}
```

### Download CSV
**GET** `/shorten/v1/urls/jobs/download/{job_id}`
**Response**
```
Returns the CSV file as a browser download.
```

### Redirect
**GET** `/r/{path}`
**Response**
```
Redirects with 302 (Found) or 404 if not found.
```

### Get URL Metric
**GET** `/analytics/v1/metrics/{short_url_path}`
**Response**
```json
{
    "id": "42dd303d-b42f-46ab-9be2-63b72c85e3f0",
    "path:": "ieo49d",
    "count": 20
}
```
## System Design & Flows

### URL Creation
Every request to create a URL, results in a unique short url being returned. For security purposes, such as phishing attacks, ShortLink does not recycle URLs. Furthermore, in the event a user submits a long URL already in the system, ShortLink will still return a new short url.

#### URL Path Generation
ShorLink URLs are a deterministic and unique 7 character path of alphanumeric characters, upper and lower case. URLs are generated by converting base 10 values to base 62. Why base 62? Because there are 62 characters in `0-9 a-z A-Z`.

Because we are generating the URLs based off a predetermined size, seven, then our base62 conversion algorithm runs at constant time `O(1)`, in theory (Does not take into account underlying PHP operations).

Every URL corresponds to a database record in the `urls` table where the primary key is an auto incrementing integer. The ID is then added with 62^7 and then its result is used to generate a base62 value which then goes on to be the URL path used to shorten a long url.

This character length gives us around **7.5 trillion URLs**, or enough to create 100 million urls per day for 96 years.

URL generation can become challenging as the number needed to create seven-character base62 values starts at 62^7 and ends at (62^8 - 1). PHP is notorious for losing precision once it reaches a certain integer, even when running in a 64-bit system. This is not due to integer overflow as (62^8 - 1) is still well below the `PHP_MAX_INT` value.

To mitigate the uncertainty of working with very large numbers, we're using the PHP binary comparison functions (`bccomp()`, `bcmod()`, and `bcdiv()`). This ensures that overflows don't turn into collisions.

#### Caching
ShortLink uses Redis for caching URLs to keep its promise of low latency redirection. Redis has been configured as an LRU cache to ensure more popular links are given higher caching priority over less popular ones.

When creating a single URL, the URL is cached immediately. The reason not only being around the speed of redirection, but also to adapt to a write master and read replica database setup where there might be some slight delay in data replication to the read replica. This makes the URLs available immediately regardless of any delay in data replication.

### CSV Job Processing
ShortLink allows users to upload a CSV to process multiple URLs. This system handles CSVs in a two-step process:
* Upload and validate the CSV
* Queue a job to process the CSV

This approach offers the following advantages:
* Because the processing is done separately, the flow is unaffected by timeout limits on the client side.
* The faster we can end the request, the faster we can allow the PHP-FPM process back into the FPM process pool.
* We can process very large files if needed. The current limit is around 20k URLs in a CSV. This number was picked arbitrarily but is configurable.

#### Web Socket Events
The two-step process nature of the flow means that we need a way to keep the user informed about the status of their CSV job. Since the request ends after file validation, and before processing, ShortLink uses Pusher web sockets to send updates to the client side about the status of the job: from the number of row currently processed, to failures or successes.

#### CSV Processing Diagram
![Shorter (Diagrams) - CSV Processing](https://github.com/user-attachments/assets/eea4d260-b6ae-4f6a-923e-31790871a772)

### URL Redirection
URL redirection is pretty straightforward. Check the cache for the url given the path, if found, get from cache, if not found, get from DB. When a URL is accessed, a Laravel `UrlVisited` event is fired, which the Analytics domains is subscribed to.

Additionally, all redirects return a `302` status code as opposed to `301`. The reason being that a `301` will make browsers skip the ShortLink hop and go directly to the long url, and this will impact analytics reporting.

![Shorter (Diagrams) - Redirection](https://github.com/user-attachments/assets/8c8d1335-63ed-4f75-b1f2-45bce187b592)

### Analytics Tracking
The Analytics domain listens to `UrlVisited` events to track all visits. For each visit, a counter is increased in Redis for that one visit. ShortLink uses Redis to track visits due to its ability to handle large throughput with minimal overhead. This gets the user to their destination faster than if we wrote to the DB on every visit.

Every minute, a cron job that flushes the redis counts runs, and updates the URL metrics to the DB, where it is then available for reading. This makes the analytics eventually consistent at a slight delay of 1 minute. Additionally, this keeps the database from becoming overwhelmed during high traffic since thousands of URL visits in a single minute would only result in a few database writes.

![Shorter (Diagrams) - Analytics](https://github.com/user-attachments/assets/fc21d852-866a-473c-9215-649b1cacc99b)

