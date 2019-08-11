<?php

namespace SimpleSAML\Auth;

interface SourceFactory
{
    /**
     * @param array $info
     * @param array $config
     * @return \SimpleSAML\Auth\Source
     */
    public function create(array $info, array $config) : Source;
}
