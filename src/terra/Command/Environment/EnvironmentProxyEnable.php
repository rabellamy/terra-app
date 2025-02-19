<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use terra\Factory\EnvironmentFactory;


class EnvironmentProxyEnable extends Command
{
  protected function configure()
  {
    $this
      ->setName('url-proxy:enable')
      ->setDescription('Enable the URL proxy allowing multiple domains')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln("Hello Terra!");
    $cmd = 'docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy';

    // Confirm removal of the app.
    $helper = $this->getHelper('question');
    $question = new ConfirmationQuestion("Run $cmd ?", false);
    if (!$helper->ask($input, $output, $question)) {
      $output->writeln('<error>Cancelled</error>');
      return;
    }
    else {

      // We must use the jwilder/nginx-proxy image to allow multiple URLs per docker host.
      $process = new Process('docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy');
      $process->run(function ($type, $buffer) {
        if (Process::ERR === $type) {
          echo 'DOCKER > ' . $buffer;
        }
        else {
          echo 'DOCKER > ' . $buffer;
        }
      });
    }
  }
}