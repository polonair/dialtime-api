<?php

namespace Polonairs\Dialtime\ApiBundle\Command;

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
            ->setName('dialtime:api:httpd-config')
            ->setDescription('Configure httpd')
            ->setDefinition(new InputDefinition([
                new InputOption('daemon', 'd', InputOption::VALUE_OPTIONAL, "Type 'apache'", "apache"),
                new InputOption('path', 'p', InputOption::VALUE_OPTIONAL, "Document root path", "/var/www"),
                new InputOption('admin', 'ah', InputOption::VALUE_OPTIONAL, "Host name for admin interface", "admin.localhost"),
                new InputOption('manager', 'nh', InputOption::VALUE_OPTIONAL, "Host name for manager interface", "manager.localhost"),
                new InputOption('master', 'mh', InputOption::VALUE_OPTIONAL, "Host name for master interface", "master.localhost"),
                new InputOption('partner', 'ph', InputOption::VALUE_OPTIONAL, "Host name for partner interface", "partner.localhost")]));
    }
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $this->config($input->getOption('admin'), $input->getOption('path'))
        $this->config($input->getOption('manager'), $input->getOption('path'))
        $this->config($input->getOption('master'), $input->getOption('path'))
        $this->config($input->getOption('partner'), $input->getOption('path'));
    }    
    public function config($host, $path, $itk = true)
    {
        file_put_contents("/etc/apache2/sites-available/${host}.conf", 
            "<VirtualHost *:80>\n\t".
                "DocumentRoot \"$path\"\n\t" .
                "ServerName $host\n\t" .
        (($itk)?("AssignUserID dialtime dialtime\n\t"):("")) .
                "<Directory \"$path\">\n\t\t" .
                    "AllowOverride All\n\t\t" .
                    "Order allow,deny\n\t\t" .
                    "Require all granted\n\t\t" .
                    "Allow from all\n\t" .
                "</Directory>\n" .
            "</VirtualHost>\n");
    }
}
