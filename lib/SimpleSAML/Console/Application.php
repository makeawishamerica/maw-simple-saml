<?php

namespace SimpleSAML\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpKernel\KernelInterface;

class Application extends BaseApplication
{
    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $inputDefinition = $this->getDefinition();
        $inputDefinition->addOption(
            new InputOption('--module', '-m', InputOption::VALUE_REQUIRED, 'The module name', $kernel->getModule())
        );
    }
}
