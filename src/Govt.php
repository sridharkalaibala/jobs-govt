<?php namespace JobBrander\Jobs\Client\Providers;

use JobBrander\Jobs\Client\Job;

class Govt extends AbstractProvider
{
    /**
     * Returns the standardized job object
     *
     * @param array $payload
     *
     * @return \JobBrander\Jobs\Client\Job
     */
    public function createJobObject($payload)
    {
        $defaults = ['id', 'position_title', 'organization_name', 'locations',
            'source', 'start_date', 'end_date', 'url', 'rate_interval_code',
            'minimum', 'maximum'];

        $payload = static::parseAttributeDefaults($payload, $defaults);

        $job = new Job([
            'id' => $payload['id'],
            'title' => $payload['position_title'],
            'source' => $payload['source'],
            'url' => $payload['url'],
        ]);

        $job->addCompanies($payload['organization_name'])
            ->addCodes($payload['rate_interval_code']);

        $job->addSalaries($payload['minimum'])
            ->addSalaries($payload['maximum'])
            ->addDates($payload['start_date'])
            ->addDates($payload['end_date']);

        if (is_array($payload['locations'])) {
            foreach ($payload['locations'] as $location) {
                $job->addLocations($location);
            }
        }

        return $job;
    }

    /**
     * Get data format
     *
     * @return string
     */
    public function getFormat()
    {
        return 'json';
    }

    /**
     * Get listings path
     *
     * @return  string
     */
    public function getListingsPath()
    {
        return null;
    }

    /**
     * Get keyword(s)
     *
     * @return string
     */
    public function getKeyword()
    {
        $keyword = ($this->keyword ? $this->keyword.' ' : null).($this->getLocation() ?: null);

        if ($keyword) {
            return $keyword;
        }

        return null;
    }

    /**
     * Get combined location
     *
     * @return string
     */
    public function getLocation()
    {
        $location = ($this->city ? $this->city.', ' : null).($this->state ?: null);

        if ($location) {
            return $location;
        }

        return null;
    }

    /**
     * Get page
     *
     * @return  string
     */
    public function getFrom()
    {
        if ($this->page) {
            $from = ($this->page - 1) * $this->count;

            if ($from) {
                return $from;
            }
        }

        return null;
    }

    /**
     * Get parameters
     *
     * @return  array
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * Get query string for client based on properties
     *
     * @return string
     */
    public function getQueryString()
    {
        $query_params = [
            'query' => 'getKeyword',
            'from' => 'getFrom',
            'size' => 'getCount',
        ];

        $query_string = [];

        array_walk($query_params, function ($value, $key) use (&$query_string) {
            $computed_value = $this->$value();
            if (!is_null($computed_value)) {
                $query_string[$key] = $computed_value;
            }
        });

        return http_build_query($query_string);
    }

    /**
     * Get url
     *
     * @return  string
     */
    public function getUrl()
    {
        $query_string = $this->getQueryString();
        return 'http://api.usa.gov/jobs/search.json?'.$query_string;
    }

    /**
     * Get http verb
     *
     * @return  string
     */
    public function getVerb()
    {
        return 'GET';
    }
}
