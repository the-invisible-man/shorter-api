# ShortLink API

![tests](https://github.com/the-invisible-man/shorter-api/actions/workflows/laravel.yml/badge.svg)
[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F61861e87-b977-4779-9a0f-0383d0c5b638%3Fdate%3D1&style=flat)](https://forge.laravel.com/servers/881138/sites/2599674)

ShortLink is a URL shortening service like TinyURL. It enables users to shorten a single URL or to upload a CSV to shorten a list of URLs. We'll go over the endpoints, system design decisions, and the corresponding flows.

## Overview
This API was built with two guiding principles: high availability, and very low latency. This was achieved by making deliberate decisions about everything from the technologies used, to the code architecture.

This system attempts to abide by the design-driven development, and is event driven in nature. There are two business domains in the world of ShortLink: URL, and Analytics. Both of these domains create a clear separation of business concerns, and avoid directly interacting with each other, instead, consuming events as the only means of communication. The events are currently Laravel events, but the system is designed such the domains could become two separate microservices communicating over a message queue.

## Business Domains

## API

## System's Design & Flows
