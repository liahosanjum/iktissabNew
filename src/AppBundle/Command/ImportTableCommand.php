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
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

class ImportTableCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:import:table')
            ->setDescription('Migrating user from old iktissab App to the new App')
            ->setHelp("This command will migrate user from old iktissab database to the new iktissab database")
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'path to the file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file_path = $input->getOption('file');
        if($file_path === '=' || $file_path === null){
            $output->writeln('<error>File must be specified</error>');
            return;
        }
        $file_path = substr($file_path,1, strlen($file_path) -1);

        if(!file_exists($file_path)){
            $output->writeln('<error>File does not exist</error>');
            return;
        }


        $doctrine = $this->getContainer()->get('doctrine');
        /**
         * @var Connection
         */
        $connection = $doctrine->getConnection();



        $handle = fopen($file_path, 'r');

        $query = '';


        $start = '/^INSERT INTO/i';
        $end = '/\);$/i';
        $output->writeln("Table Import Started");
        $queries_executed = 0;
        while (($line = fgets($handle)) != false ){
            $start_line = preg_match($start, $line);
            $end_line = preg_match($end, $line);

            if($start_line && $end_line){
                $connection->exec($line);
                continue;
            }


            if($end_line){
                $query .= $line;
                $queries_executed++;
                //$output->writeln($query);
                $connection->exec($query);
                unset($query);
                $query = '';
                continue;
            }

            $query .= $line;
        }
        fclose($handle);

        $output->writeln('Queries Executed: ' . $queries_executed);
        $output->writeln("Table Imported");


    }
}