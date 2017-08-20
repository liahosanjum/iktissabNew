<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 7/19/17
 * Time: 8:06 AM
 */

namespace AppBundle\Command;


use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateUserCommand extends ContainerAwareCommand
{
    private $migration_requirments = [
        'You must have a table "iktissab_users" (users table) and "iktissab_profile_values" (profile_values table) From old iktissab',
        'User table mus be empty'
        ];

    private $migration_steps = [
        "Step1"=>'Filter profile_value table by removing all entries except iktissab card and registeration source',
        'Step2'=>[
            'Drop the table if EXISTS user_with_card, user_with_dup_card',
            'Create the tables user_with_card, user_with_dup_card'
        ],
        'Step3'=>"Populate User With Card",
        'Step4'=>'Delete Invalid Cards',
        'Step5'=>'Populate user_with_dup_card from user_with_card which are duplicated and delete the duplicate from user_with_card',
        'Step6'=>'Screen user_with_dup_card by keeping only those record which are login recently',
        'Step7'=>'Populate new iktissab user table form user_with_card and user_with_dup_card',
        'Step8'=>'Update the country column of iktissab user table for egypt cards',
        'Step9'=>'Drop user_with_card, user_with_dup_card tables'
    ];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this->setName('app:migrate-user')
            ->setDescription('Migrating user from old iktissab App to the new App')
            ->setHelp("This command will migrate user from old iktissab database to the new iktissab database");
    }

    private function Required(){
        $stm = $this->connection->query('SHOW TABLES');
        $rows = $stm->fetchAll(\PDO::FETCH_ASSOC);
        $tables = [];
        foreach ($rows as $row){
            foreach ($row as $table) $tables[] = $table;
        }


        if(!in_array('iktissab_users', $tables) || !in_array('iktissab_profile_values', $tables)){
            $this->output->writeln('<error>' . $this->migration_requirments[0].'</error>');
            return false;
        }

        $stm = $this->connection->query("SELECT count(*) as counter FROM user");
        $stmRow = $stm->fetchAll();
        if($stmRow[0]['counter']>0){
            $this->output->writeln("<error>The user table must be empty</error>");
            return false;
        }


        return true;
    }

    private function Step1(){
        $this->connection->exec('DELETE FROM iktissab_profile_values where `iktissab_profile_values`.`fid` NOT in (1,14)');
    }

    private function Step2(){
        $create_query = "CREATE TABLE `%s` (
                  `uid` bigint(20) NOT NULL,
                  `ikt_card_no` int(11) DEFAULT NULL,
                  `email` varchar(255) NOT NULL,
                  `reg_date` int(11) NOT NULL,
                  `last_login` int(11) DEFAULT NULL,
                  `password` varchar(255) NOT NULL,
                  `activation_source` varchar(1) DEFAULT NULL,
                  `data` varchar(800) DEFAULT NULL,
                  `country` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`uid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $this->connection->exec('DROP TABLE IF EXISTS user_with_card');
        $this->connection->exec('DROP TABLE IF EXISTS user_with_dup_card');

        $this->connection->exec(sprintf($create_query, 'user_with_card'));
        $this->connection->exec(sprintf($create_query, 'user_with_dup_card'));
    }
    private function Step3(){
        $io = new SymfonyStyle($this->input, $this->output);
        $insert_query  = "INSERT INTO user_with_card
            SELECT
              u.uid,
              (select f1.value from iktissab_profile_values as f1 where f1.uid=u.uid AND f1.fid=1 AND f1.value REGEXP '^[[:digit:]]{8}') as ikt_card_no,
              u.mail as email,
              u.created as reg_date,
              u.login as last_login,
              u.pass as password,
              (select f2.value from iktissab_profile_values as f2 where f2.uid=u.uid AND f2.fid=14) as activation_source,
              '' as data,
              'sa' as country
            FROM `iktissab_users`as u limit ?, ?";

        $limit = 2000;
        $counter_statement = $this->connection->query("SELECT count(*) as counter FROM iktissab_users");
        $total = $counter_statement->fetchColumn(0);
        $this->output->writeln("TOTAL USERS FOUND: " . $total);
        $total = ceil($total/$limit);
        $this->output->writeln('');
        $insert_statement = $this->connection->prepare($insert_query);
        $this->output->writeln("START INSERTION");

        //progress bar
        $progressBar = $io->createProgressBar(100);

        for ($i=0; $i<$total; $i++){
            $insert_statement->bindValue(1, $i*$limit, 'integer');
            $insert_statement->bindValue(2, $limit, 'integer');
            $insert_statement->execute();
            $progressBar->setProgress(floor($i*100/$total));
        }

        $progressBar->finish();
        $io->newLine();
    }

    private function Step4(){
        $this->connection->exec('DELETE FROM user_with_card where length(ikt_card_no) <> 8 OR ikt_card_no is NULL');
    }

    /**
     * @return array
     */
    private function Step5(){
        $filter_duplicate_query = "SELECT ikt_card_no, count(ikt_card_no) as counter FROM user_with_card group by ikt_card_no HAVING counter > 1";

        $filter_statement = $this->connection->query($filter_duplicate_query);
        $rows = $filter_statement->fetchAll();
        $dup_cards = [];
        foreach ($rows as $row){
            $dup_cards[] = $row['ikt_card_no'];
        }

        $this->connection->exec(sprintf("INSERT INTO user_with_dup_card SELECT * FROM user_with_card WHERE ikt_card_no IN(%s)", implode(",", $dup_cards)));
        $this->connection->exec(sprintf("DELETE FROM user_with_card WHERE ikt_card_no IN(%s)", implode(',', $dup_cards)));

        return $rows;
    }
    private function Step6($rows){

        $io = new SymfonyStyle($this->input, $this->output);
        $progressBar = $io->createProgressBar(100);
        $total = count($rows);
        $i = 0;

        foreach ($rows as $row){

            $ikt_card_no = $row['ikt_card_no'];
            $delete_duplicate_query = "DELETE  FROM user_with_dup_card WHERE ikt_card_no = '$ikt_card_no' AND last_login=0";
            $this->connection->exec($delete_duplicate_query);

            $select_query = "SELECT * FROM user_with_dup_card WHERE ikt_card_no = '$ikt_card_no'";
            $statement = $this->connection->query($select_query);
            $dupRows = $statement->fetchAll();
            if($statement->rowCount()>1){
                $max_query = "SELECT MAX(last_login) as latest FROM user_with_dup_card WHERE ikt_card_no='$ikt_card_no'";
                $max_statement = $this->connection->query($max_query);
                $max = $max_statement->fetchColumn(0);

                //delete others
                $delete_duplicate_query = "DELETE  FROM user_with_dup_card WHERE ikt_card_no = '$ikt_card_no' AND last_login<$max";
                $this->connection->exec($delete_duplicate_query);

            }

            $progressBar->setProgress(floor($i*100/$total));

            $i++;


        }
        $progressBar->finish();
        $io->newLine();

    }
    private function Step7(){
        $limit = 2000;
        $io = new SymfonyStyle($this->input, $this->output);

        $counter_statement = $this->connection->query("SELECT count(*) as counter FROM user_with_card");
        $total = ceil($counter_statement->fetchColumn(0)/$limit);

        $insert_user_query = "INSERT INTO user(`ikt_card_no`,`email`,`reg_date`,`last_login`,`password`,`activation_source`,`data`,`country`,`status`,`modified`) 
            SELECT `ikt_card_no`, `email`, `reg_date`, `last_login`, `password`, `activation_source`, `data`, `country`, 1 as `status`, null as `modified` FROM user_with_card limit ?, ?";
        $insert_user_statement = $this->connection->prepare($insert_user_query);

        $progressBar = $io->createProgressBar(100);
        for ($i=0; $i<$total; $i++){
            $insert_user_statement->bindValue(1, $i*$limit, 'integer');
            $insert_user_statement->bindValue(2, $limit, 'integer');
            $insert_user_statement->execute();
            $progressBar->setProgress(floor($i*100/$total));
        }

        $progressBar->finish();
        $io->newLine();

        //add row from duplicate table
        $insert_user_query = "INSERT INTO user(`ikt_card_no`,`email`,`reg_date`,`last_login`,`password`,`activation_source`,`data`,`country`,`status`,`modified`)
            SELECT `ikt_card_no`, `email`, `reg_date`, `last_login`, `password`, `activation_source`, `data`, `country`, 1 as `status`, null as `modified` FROM user_with_dup_card";
        $this->connection->exec($insert_user_query);
    }
    private function Step8(){
        $this->connection->exec("UPDATE user SET country='eg' WHERE ikt_card_no LIKE '5%'");
    }
    private function Step9(){
        $this->connection->exec('DROP TABLE IF EXISTS user_with_card');
        $this->connection->exec('DROP TABLE IF EXISTS user_with_dup_card');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->input = $input;
        $this->output = $output;
        $doctrine = $this->getContainer()->get('doctrine');
        $this->connection = $doctrine->getConnection();

        if(!$this->Required()){
            return;
        }


        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step1']));
        $this->Step1();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step2'][0]));
        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step2'][1]));
        $this->Step2();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step3']));
        $this->Step3();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step4']));
        $this->Step4();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step5']));
        $data_row = $this->Step5();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step6']));
        $this->Step6($data_row);

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step7']));
        $this->Step7();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step8']));
        $this->Step8();

        $output->writeln(sprintf('<info>%s</info>', $this->migration_steps['Step9']));
        $this->Step9();

        $output->writeln('<info>Data Migration is done</info>');
        $output->writeln('<comment>You have to remove the iktissab_users and iktissab_profile_values</comment>');


    }
}