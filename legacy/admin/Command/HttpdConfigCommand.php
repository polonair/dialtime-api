<?php

namespace Polonairs\Dialtime\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class HttpdConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dialtime:admin:httpd-config')
            ->setDescription('Configure httpd')
            ->setDefinition(new InputDefinition([
                new InputOption('daemon', 'd', InputOption::VALUE_OPTIONAL, "Type 'apache'", "apache"),
                new InputOption('path', 'p', InputOption::VALUE_OPTIONAL, "Document root path", "/var/www"),
                new InputOption('host', 'hn', InputOption::VALUE_OPTIONAL, "Host name for admin interface", "admin.localhost")]));
    }
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $this
            ->getContainer()
            ->get('dialtime.web.apache_configurer')
            ->configure(
                $input->getOption('host'), 
                $input->getOption('path'));
    }
}
