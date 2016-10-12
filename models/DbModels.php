<?php

/* 数据库操作公共类
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\models;

class DbModels {

    /**
     * @param type $connection
     * @param type $tableName 表名
     * @param type $column 列名
     * @param type $searchParams 查询条件
     * @param type $currentPage 当前页
     * @param type $pageSize 每页条数
     * @param type $orderBy 排序
     * @param type $key 主键，查询总条数用
     * @return type
     */
    public function queryListByTableName($connection, $tableName, $column, $searchParams, $currentPage = 1, $pageSize = 15, $orderBy = '', $key = '') {
        $resultArray = array();
        $pager = array();
        $sqlWhere = $this->jointSearchParams($searchParams);
        //查询记录总条数
        $totalRecord = $this->queryCount($connection, $tableName, $sqlWhere, $searchParams, $key);
        $pager['currentPage'] = $currentPage;
        $pager['pageSize'] = $pageSize;
        $pager['totalRecord'] = $totalRecord;
        $resultArray['pager'] = $pager;
        if (($currentPage - 1) * $pageSize > $pager['totalRecord']) {
            $resultArray['data'] = array();
            return $resultArray;
        }

        //查询记录
        if (!empty($column)) {
            $columns = implode(',', $column);
        } else {
            $columns = ' * ';
        }
        $sql = 'select ' . $columns . ' from ' . $tableName . $sqlWhere;
        $resultArray['data'] = $this->queryAllCommand($connection, $sql, $searchParams, $currentPage, $pageSize, $orderBy);
        return $resultArray;
    }

    /**
     * 执行列表查询
     * @param type $connection
     * @param type $sql
     * @param type $searchParams
     * @param type $currentPage
     * @param type $pageSize
     * @param type $orderBy 排序
     * @return type
     */
    private function queryAllCommand($connection, $sql, $searchParams, $currentPage, $pageSize, $orderBy) {
        $command = $connection->createCommand($sql . ' ' . $orderBy . " LIMIT :offset,:limit");
        $command->bindValue(':offset', ($currentPage - 1) * $pageSize);
        $command->bindValue(':limit', $pageSize);
        foreach ($searchParams as $k => $v) {
            foreach ($searchParams as $k => $v) {
                if ($k == 'equal') {
                    foreach ($searchParams[$k] as $k2 => $v2) {
                        $command->bindValue(":" . $k2, $v2);
                    }
                } else {
                    if ($k == 'like') {
                        foreach ($searchParams[$k] as $k2 => $v2) {
                            $command->bindValue(":" . $k2, '%' . $v2 . '%');
                        }
                    } else {
                        if ($k == 'llike') {
                            foreach ($searchParams[$k] as $k2 => $v2) {
                                $command->bindValue(":" . $k2, $v2 . '%');
                            }
                        } else {
                            if ($k == 'between') {
                                foreach ($searchParams[$k] as $k2 => $v2) {
                                    if (isset($v2['startTime']) && !empty($v2['startTime'])) {
                                        $command->bindValue(":" . $k2 . '_startTime', $v2['startTime']);
                                    }
                                    if (isset($v2['endTime']) && !empty($v2['endTime'])) {
                                        $command->bindValue(":" . $k2 . '_endTime', $v2['endTime']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $datas = $command->queryAll();
        return $datas;
    }

    /**
     * 拼接查询sql的where语句
     * @param type $searchParams
     * @return string
     */
    private function jointSearchParams($searchParams) {
        $sql = '';
        if (!empty($searchParams)) {
            $sql .= ' where ';
            foreach ($searchParams as $k => $v) {
                if ($k == 'equal') {
                    foreach ($searchParams[$k] as $k2 => $v2) {
                        $sql.=' ' . $k2 . ' = :' . $k2 . ' and';
                    }
                } else {
                    if ($k == 'like') {
                        foreach ($searchParams[$k] as $k2 => $v2) {
                            $sql.=' ' . $k2 . ' like :' . $k2 . ' and';
                        }
                    } else {
                        if ($k == 'llike') {
                            foreach ($searchParams[$k] as $k2 => $v2) {
                                $sql.=' ' . $k2 . ' like :' . $k2 . ' and';
                            }
                        } else {
                            if ($k == 'between') {
                                foreach ($searchParams[$k] as $k2 => $v2) {
                                    if (isset($v2['startTime']) && !empty($v2['startTime'])) {
                                        $sql.=' ' . $k2 . ' >= :' . $k2 . '_startTime and';
                                    }
                                    if (isset($v2['endTime']) && !empty($v2['endTime'])) {
                                        $sql.=' ' . $k2 . ' < :' . $k2 . '_endTime and';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (substr($sql, 0 - strlen('and')) == 'and') {
            $sql = substr($sql, 0, strlen($sql) - strlen('and'));
        }
        return $sql;
    }

    /**
     * 查询总记录条数
     * @param type $connection数据链接信息
     * @param type $tableName主表名称
     * @param type $searchParams查询条件
     * @param type $key主键
     * @return type
     */
    private function queryCount($connection, $tableName, $sqlWhere, $searchParams, $key) {
        //查询总条数
        $countSql = '';
        if (empty($key)) {
            $countSql = 'select count(id) from ' . $tableName;
        } else {
            $countSql = 'select count(' . $key . ') from ' . $tableName;
        }
        $countSql .= $sqlWhere;
        $commandPage = $connection->createCommand($countSql);
        foreach ($searchParams as $k => $v) {
            foreach ($searchParams as $k => $v) {
                if ($k == 'equal') {
                    foreach ($searchParams[$k] as $k2 => $v2) {
                        $commandPage->bindValue(":" . $k2, $v2);
                    }
                } else {
                    if ($k == 'like') {
                        foreach ($searchParams[$k] as $k2 => $v2) {
                            $commandPage->bindValue(":" . $k2, '%' . $v2 . '%');
                        }
                    } else {
                        if ($k == 'llike') {
                            foreach ($searchParams[$k] as $k2 => $v2) {
                                $commandPage->bindValue(":" . $k2, $v2 . '%');
                            }
                        } else {
                            if ($k == 'between') {
                                foreach ($searchParams[$k] as $k2 => $v2) {
                                    if (isset($v2['startTime']) && !empty($v2['startTime'])) {
                                        $commandPage->bindValue(":" . $k2 . '_startTime', $v2['startTime']);
                                    }
                                    if (isset($v2['endTime']) && !empty($v2['endTime'])) {
                                        $commandPage->bindValue(":" . $k2 . '_endTime', $v2['endTime']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $countTotal = $commandPage->queryScalar();
        return $countTotal;
    }

    /**
     * 查询记录详情
     * @param type $connection 数据链接信息
     * @param type $tableName 主表名称
     * @param type $column 查询列
     * @param type $searchParams 查询条件
     * @return type
     */
    public function queryDetailByTableName($connection, $tableName, $column, $searchParams) {
        $connection = \Yii::$app->db;
        //查询记录
        if (!empty($column)) {
            $columns = implode(',', $column);
        } else {
            $columns = ' * ';
        }
        $sql = 'select ' . $columns . ' from ' . $tableName;
        if (!empty($searchParams)) {
            $sql .= ' where ';
            foreach ($searchParams as $k => $v) {
                if (!empty($v)) {
                    $sql.=' ' . $k . ' = :' . $k . ' and';
                }
            }
            if (substr($sql, 0 - strlen('and')) == 'and') {
                $sql = substr($sql, 0, strlen($sql) - strlen('and'));
            }
        }
        $commandPage = $connection->createCommand($sql);

        foreach ($searchParams as $k => $v) {
            if (!empty($v)) {
                $commandPage->bindValue(":" . $k, $v);
            }
        }

        $datas = $commandPage->queryOne();
        return $datas;
    }

    /**
     * 查询原生sql：select
     * @param type $connection数据链接信息
     * @param type $sql
     * @param type $param
     * @return null
     */
    public function selectBySql($connection, $sql) {
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * 执行原生sql：create，update，delete
     * @param type $connection 数据链接信息
     * @param type $sql
     * @param type $param
     * @param type $returnId 返回自增ID
     * @return null
     */
    public function execute($connection, $sql, $param, $returnId = false) {
        $command = $connection->createCommand($sql);
        foreach ($param as $k => $v) {
            $command->bindValue(":" . $k, $v);
        }
        $result = $command->execute();
        if ($returnId && $result) {
            $id = $connection->getLastInsertID();
        }
        return $id;
    }

}
