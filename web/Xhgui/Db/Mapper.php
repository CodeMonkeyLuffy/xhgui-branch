<?php

class Xhgui_Db_Mapper
{

    /**
     * Convert request data keys into mongo values.
     *
     * @param array $options
     * @return array
     */
    public function convert($options)
    {
        $result = array(
            'conditions' => array(),
            'sort' => null,
            'perPage' => 25
        );
        if (isset($options['conditions'])) {
            $result['conditions'] = $this->_conditions($options['conditions']);
        }
        $result['sort'] = $this->_sort($options);

        if (isset($options['perPage'])) {
            $result['perPage'] = $options['perPage'];
        }

        return $result;
    }

    /**
     * Convert the search parameters into the matching fields.
     *
     * Keeps the schema details out of the GET parameters.
     * String casts are uses to prevent mongo operator injection.
     *
     * @param array $search
     * @return array
     */
    protected function _conditions($search)
    {
        $conditions = array();
        if (isset($search['date_start'])) {
            $conditions['meta.request_date']['$gte'] = (string)$search['date_start'];
        }
        if (isset($search['date_end'])) {
            $conditions['meta.request_date']['$lte'] = (string)$search['date_end'];
        }
        if (isset($search['simple_url'])) {
            $conditions['meta.simple_url'] = (string)$search['simple_url'];
        }
        if (isset($search['url'])) {
            // Not sure if letting people use regex here
            // is a good idea. Only one way to find out.
            $conditions['meta.url'] = array(
                '$regex' => (string)$search['url'],
                '$options' => 'i',
            );
        }
        return $conditions;
    }

    /**
     * Get sort options for a paginated set.
     *
     * Whitelists to valid known keys.
     *
     * @param array $options Pagination options including the sort key.
     * @return array Sort field & direction.
     */
    protected function _sort($options)
    {
        $direction = -1;
        $validDirections = array('desc' => -1, 'asc' => 1);
        if (
            isset($options['direction']) &&
            isset($validDirections[$options['direction']])
        ) {
            $direction = $validDirections[$options['direction']];
        }

        $valid = array('wt', 'mu', 'cpu');
        if (
            empty($options['sort']) ||
            (isset($options['sort']) && !in_array($options['sort'], $valid))
        ) {
            return array('meta.SERVER.REQUEST_TIME' => $direction);
        }
        if ($options['sort'] == 'wt') {
            return array('profile.main().wt' => $direction);
        } elseif ($options['sort'] == 'mu') {
            return array('profile.main().mu' => $direction);
        } elseif ($options['sort'] == 'cpu') {
           return array('profile.main().cpu' => $direction);
        }
    }

}
