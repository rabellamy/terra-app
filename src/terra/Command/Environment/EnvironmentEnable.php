<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

use terra\Factory\EnvironmentFactory;

// ...

class EnvironmentEnable extends Command
{
  protected function configure()
  {
    $this
      ->setName('environment:enable')
      ->setDescription('Enable environment.')
      ->addArgument(
        'app_name',
        InputArgument::OPTIONAL,
        'The name the app to enable.'
      )
      ->addArgument(
        'environment_name',
        InputArgument::OPTIONAL,
        'The name the environment to enable.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // If there are no apps, return
    if (count($this->getApplication()->getTerra()->getConfig()->get('apps')) == 0) {
      $output->writeln("<comment>There are no apps to remove!</comment>");
      $output->writeln("Use the command <info>terra app:add</info> to add your first app.");
      return;
    }

    $helper = $this->getHelper('question');
    $app_name = $input->getArgument('app_name');
    $environment_name = $input->getArgument('environment_name');

    // If no name specified provide options
    if (empty($app_name)) {
      $question = new ChoiceQuestion(
        'Which app? ',
        array_keys($this->getApplication()->getTerra()->getConfig()->get('apps')),
        NULL
      );
      $app_name = $helper->ask($input, $output, $question);
    }

    $app = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);

    // If no environments:
    if (count(($app['environments'])) == 0) {
      $output->writeln("<comment>There are no environments for the app $app_name!</comment>");
      $output->writeln("Use the command <info>terra environment:add</info> to add your first environment.");
      return;
    }

    // If no environment name specified provide options
    if (empty($environment_name)) {
      $question = new ChoiceQuestion(
        'Which environment? ',
        array_keys($app['environments']),
        NULL
      );
      $environment_name = $helper->ask($input, $output, $question);
    }

    $environment = $app['environments'][$environment_name];

    $environment_factory = new EnvironmentFactory($environment, $app);
    $output->writeln($environment_factory->enable());

    $port = $environment_factory->getPort();
    $port = array_pop(explode(':', $port));

    $app['environments'][$environment_name]['url'] = "http://localhost:$port";
    $this->getApplication()->getTerra()->getConfig()->add('apps', $app_name, $app);

    if ($this->getApplication()->getTerra()->getConfig()->save()) {
      $output->writeln('<info>Environment enabled!</info>  Available at http://' . $environment_factory->getUrl());
    }
    else {
      $output->writeln('<error>Environment info not saved.</error>');
    }
  }
}