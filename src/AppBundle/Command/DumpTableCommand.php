<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 19/07/2017
 * Time: 5:01 PM
 */
namespace AppBundle\Command;


    use Doctrine\DBAL\Connection;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

class DumpTableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:dump-table')
            ->setDescription('Migrating user from old iktissab App to the new App')
            ->setHelp("This command will migrate user from old iktissab database to the new iktissab database");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        /**
         * @var Connection
         */
        $connection = $doctrine->getConnection();

        $file = str_replace('src'.DIRECTORY_SEPARATOR.'AppBundle'.DIRECTORY_SEPARATOR.'Command', 'web'. DIRECTORY_SEPARATOR .'user.sql', __DIR__);

        $handle = fopen($file, 'r');

        $re = '/^INSERT INTO/i';
        $output->writeln("Table Import Started");
        $queries_executed = 0;
        while (($line = fgets($handle)) != false ){
            if(preg_match($re, $line)){
                $queries_executed++;
                $connection->exec($line);

            }
            unset($line);
        }
        fclose($handle);

        $output->writeln('Queries Executed: ' . $queries_executed);
        $output->writeln("Table Imported");


    }
}