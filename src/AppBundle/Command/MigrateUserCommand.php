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
    protected function configure()
    {
        $this->setName('app:migrate-user')
            ->setDescription('Migrating user from old iktissab App to the new App')
            ->setHelp("This command will migrate user from old iktissab database to the new iktissab database");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $doctrine = $this->getContainer()->get('doctrine');


        /**
         * @var Connection
         */
        $connection = $doctrine->getConnection();

        $stm = $connection->query("SELECT count(*) as counter FROM user");
        $stm->fetchAll();
        if($stm->rowCount()>0){
            $output->writeln("FIRST TRUNCATE USER TABLE THEN RUN THIS SCRIPT");
            return;
        }



        $drop_query = "DROP TABLE IF EXISTS %s";

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

        $insert_query  = "INSERT INTO user_with_card
            SELECT
              u.uid,
              (select f1.value from profile_values as f1 where f1.uid=u.uid AND f1.fid=1 AND f1.value REGEXP '^[[:digit:]]{8}') as ikt_card_no,
              u.mail as email,
              u.created as reg_date,
              u.login as last_login,
              u.pass as password,
              (select f2.value from profile_values as f2 where f2.uid=u.uid AND f2.fid=14) as activation_source,
              '' as data,
              'sa' as country
            FROM `users`as u limit ?, ?";

        $delete_invalid_card_query = "DELETE FROM user_with_card where length(ikt_card_no) <> 8 OR ikt_card_no is NULL";

        $filter_duplicate_query = "SELECT ikt_card_no, count(ikt_card_no) as counter FROM user_with_card group by ikt_card_no HAVING counter > 1";

        $connection->exec(sprintf($drop_query, 'user_with_card'));
        $connection->exec(sprintf($drop_query, 'user_with_dup_card'));
        $connection->exec(sprintf($create_query, 'user_with_card'));
        $connection->exec(sprintf($create_query, 'user_with_dup_card'));

        //start insert

        $limit = 2000;
        $counter_statement = $connection->query("SELECT count(*) as counter FROM users");
        $total = $counter_statement->fetchColumn(0);
        $output->writeln("TOTAL USERS FOUND: " . $total);
        $total = ceil($total/$limit);
        $output->writeln('');
        $insert_statement = $connection->prepare($insert_query);
        $output->writeln("START INSERTION");

        //progress bar
        $io->progressStart($total);

        for ($i=0; $i<$total; $i++){
            $insert_statement->bindValue(1, $i*$limit, 'integer');
            $insert_statement->bindValue(2, $limit, 'integer');
            $insert_statement->execute();
            $io->progressAdvance($i);
        }

        $io->progressFinish();
        $io->newLine();

        $output->writeln($delete_invalid_card_query);
        $result = $connection->exec($delete_invalid_card_query);
        $output->writeln("DELETE INVALID CARD QUERY RESULT: " . $result);

        $output->writeln($filter_duplicate_query);
        $filter_statement = $connection->query($filter_duplicate_query);
        $rows = $filter_statement->fetchAll();
        $dup_cards = [];
        foreach ($rows as $row){
            $dup_cards[] = $row['ikt_card_no'];
        }

        $connection->exec(sprintf("INSERT INTO user_with_dup_card SELECT * FROM user_with_card WHERE ikt_card_no IN(%s)", implode(",", $dup_cards)));
        $connection->exec(sprintf("DELETE FROM user_with_card WHERE ikt_card_no IN(%s)", implode(',', $dup_cards)));


        //fill user_with_dup from duplicate data


        $output->writeln('SCREENING DATA START');

        foreach ($rows as $row){

            $ikt_card_no = $row['ikt_card_no'];
            $delete_duplicate_query = "DELETE  FROM user_with_dup_card WHERE ikt_card_no = '$ikt_card_no' AND last_login=0";
            $connection->exec($delete_duplicate_query);

            $select_query = "SELECT * FROM user_with_dup_card WHERE ikt_card_no = '$ikt_card_no'";
            $statement = $connection->query($select_query);
            $dupRows = $statement->fetchAll();
            if($statement->rowCount()>1){
                $max_query = "SELECT MAX(last_login) as latest FROM user_with_dup_card WHERE ikt_card_no='$ikt_card_no'";
                $max_statement = $connection->query($max_query);
                $max = $max_statement->fetchColumn(0);

                //delete others
                $delete_duplicate_query = "DELETE  FROM user_with_dup_card WHERE ikt_card_no = '$ikt_card_no' AND last_login<$max";
                $connection->exec($delete_duplicate_query);

            }

            $output->writeln("DUPLICATE ROW FOR CARD NO : $ikt_card_no are DELETED");

        }
        $output->writeln('SCREENING DATA FINISH');


        $truncate_query = "TRUNCATE TABLE user";
        $output->writeln("USER TABLE IS TRUNCATED");
        $connection->exec($truncate_query);

        $counter_statement = $connection->query("SELECT count(*) as counter FROM user_with_card");
        $total = ceil($counter_statement->fetchColumn(0)/$limit);
        //$output->writeln("TOTAL USERS AFTER SCREENING: " . $total);

        $insert_user_query = "INSERT INTO user SELECT ikt_card_no, email, reg_date, last_login, password, activation_source, data, country FROM user_with_card limit ?, ?";
        $insert_user_statement = $connection->prepare($insert_user_query);
        $output->writeln("DATA INSERTION START");
        $io->newLine();
        $io->progressStart($total);
        for ($i=0; $i<$total; $i++){
            $insert_user_statement->bindValue(1, $i*$limit, 'integer');
            $insert_user_statement->bindValue(2, $limit, 'integer');
            $insert_user_statement->execute();
            $io->progressAdvance($i);
        }

        $io->progressFinish();


        //add row from duplicate table
        $insert_user_query = "INSERT INTO user SELECT ikt_card_no, email, reg_date, last_login, password, activation_source, data, country FROM user_with_dup_card";
        $connection->exec($insert_user_query);

        $connection->exec("UPDATE user SET country='eg' WHERE ikt_card_no LIKE '5%'");

        $output->writeln("DATA INSERTION COMPLETE");

        $connection->exec(sprintf($drop_query, 'user_with_card'));
        $connection->exec(sprintf($drop_query, 'user_with_dup_card'));

        $output->writeln("EXPORT DONE");


    }
}