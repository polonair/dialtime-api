<?php

namespace Polonairs\Dialtime\CombineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

class HttpdConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dialtime:combine:httpd-config')
            ->setDescription('Configure httpd')
            ->setDefinition(new InputDefinition([
                new InputOption('daemon', 'd', InputOption::VALUE_OPTIONAL, "Type 'apache'", "apache"),
                new InputOption('path', 'p', InputOption::VALUE_OPTIONAL, "Document root path", "/var/www"),
                new InputOption('admin', 'ah', InputOption::VALUE_OPTIONAL, "Host name for admin interface", "admin.localhost"),
                new InputOption('master', 'mh', InputOption::VALUE_OPTIONAL, "Host name for master interface", "master.localhost"),
                new InputOption('partner', 'ph', InputOption::VALUE_OPTIONAL, "Host name for partner interface", "partner.localhost")]));
    }
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $app = $this->getApplication();

        $app->find('dialtime:admin:httpd-config')->run(new ArrayInput([
            'command' => 'dialtime:admin:httpd-config',
            '--daemon'  => $input->getOption('daemon'),
            '--host'    => $input->getOption('admin'),
            '--path'    => $input->getOption('path'),
        ]), $output);
        $app->find('dialtime:master:httpd-config')->run(new ArrayInput([
            'command' => 'dialtime:master:httpd-config',
            '--daemon'  => $input->getOption('daemon'),
            '--host'    => $input->getOption('master'),
            '--path'    => $input->getOption('path'),
        ]), $output);
        $app->find('dialtime:partner:httpd-config')->run(new ArrayInput([
            'command' => 'dialtime:partner:httpd-config',
            '--daemon'  => $input->getOption('daemon'),
            '--host'    => $input->getOption('partner'),
            '--path'    => $input->getOption('path'),
        ]), $output);
     }
}
