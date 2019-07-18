<?php

namespace app\admin\model;

use \PDO;
use think\Db;
use think\exception\PDOException;
use think\Model;

class PhwPushData extends Model
{
    private $dbMssql;
    private $dbMysql;

    public function handlePhwData()
    {
        set_time_limit(0);
        try {
            $this->dbMysql = new \PDO('mysql:host=localhost;dbname=72crm', 'root', 'root');
        } catch (PDOException $e) {
            print $e->getMessage();
        }

        try {
            $this->dbMssql = new \PDO("sqlsrv:server=101.231.197.78;Database=Test", 'test', 'a*1');
            $sql = <<<EOD
            SELECT TOP 10 
                c.intCustomerId,
                c.vchCustFullName,
                c.vchContract,
                c.vchPhone1,
                c.vchFax,
                c.vchMobile,
                c.intLevel,
                c.vchAddress,
                c.intOrigin,
                o.vchOriginDesc,
                c.vchKeepBy,
                e.vchUserId
            FROM
                phsCustomers AS c
            LEFT JOIN phsEmployee AS e ON c.vchKeepBy = e.intEmpId
            LEFT JOIN phsCustOrigins AS o ON c.intOrigin = o.intOriginId
EOD;

            $id = self::getLastId($this->dbMysql);
            if ($id) {
                $condition = ' where intCustomerId >' . $id;
                $sql = $sql . $condition;
            }

            $statementSqlServer = $this->dbMssql->prepare($sql);
            $statementSqlServer->execute();

            $statementSqlServer->bindColumn('vchCustFullName', $name);
            $statementSqlServer->bindColumn('vchOriginDesc', $source);
            $statementSqlServer->bindColumn('vchPhone1', $telephone);
            $statementSqlServer->bindColumn('vchMobile', $mobile);
            $statementSqlServer->bindColumn('vchAddress', $address);
            $statementSqlServer->bindColumn('intCustomerId', $id);

            $time = time();

            while ($row = $statementSqlServer->fetch(PDO::FETCH_BOUND)) {
                echo '<br/>';
                $sqlCrm = "insert into 5kcrm_crm_customer (name, is_lock, deal_status, deal_time, source, telephone, mobile,create_user_id, owner_user_id, ro_user_id, address, create_time, update_time) values ('{$name}', 0, '未成交', {$time}, '{$source}', '{$telephone}', '{$mobile}', 1, 0, ',1,', '{$address}', '{$time}', '{$time}')";
                echo 'intCustomerId: ' . $id . '<br/>';
                echo $sqlCrm;
                echo '<hr/>';

                try {
                    $statementMysql = $this->dbMysql->prepare($sqlCrm);
                    $statementMysql->execute();
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
            }

            $data = ['intCustomerId' => $id, 'create_time' => time()];
            Db::table('phw_sync_data')->insert($data);

            $dbMysql = null;
            $dbSqlServer = null;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    private function getLastId($db)
    {
        $sqlCrm = "select intCustomerId from phw_sync_data order by id desc limit 1";
        $statement = $db->prepare($sqlCrm);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row['intCustomerId'];
    }
}