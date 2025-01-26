SELECT
    cl.id,
    cl.first_name,
    cl.last_name,
    lf."name" as law_firm,
    prv."name" as provider,
    agg.total_treatments,
    agg.total_provider_rate,
    agg.total_lp_rate,
    agg.total_lop_rate
FROM (
         SELECT
             SUM(visits.total_provider_rate) as total_provider_rate,
             SUM(visits.total_lp_rate) as total_lp_rate,
             SUM(visits.total_lop_rate) as total_lop_rate,
             visits.client_id,
             visits.law_firm_id,
             COUNT(*) as total_treatments
         FROM visits
         GROUP BY visits.client_id, visits.law_firm_id
     ) agg
         LEFT JOIN visits v ON v.client_id = agg.client_id AND v.law_firm_id = agg.law_firm_id
         LEFT JOIN law_firms lf ON v.law_firm_id = lf.id
         LEFT JOIN providers prv ON v.provider_id = prv.id
         LEFT JOIN clients cl ON v.client_id = cl.id
