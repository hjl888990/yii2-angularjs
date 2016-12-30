<?php

namespace app\controllers;


use yii\web\Controller;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class TestController extends Controller {
 
    public function actionSale() {
        $conn = mysql_connect("172.21.104.1:3306", "web", "123456");
        if (!$conn) {
            echo "connect failed";
            exit;
        }
        mysql_select_db("hjl", $conn);
        mysql_query("set names utf8");

        $create_time=  date("Y-m-d H:i:s") ;
        $goods_id = 1001;
        $number = 1;


        $sql = "select stock from stock where sku='$goods_id'"; //解锁 此时ih_store数据中goods_id='$goods_id' and sku_id='$sku_id' 的数据被锁住(注3)，其它事务必须等待此次事务 提交后才能执行
        $rs = mysql_query($sql, $conn);
        $row = mysql_fetch_assoc($rs);
        if ($row['stock'] > 0) {//高并发下会导致超卖           

            //库存减少
            $sql = "update stock set stock=stock-{$number} where sku='$goods_id' and stock>0";
            $store_rs = mysql_query($sql, $conn);
            if (mysql_affected_rows()) {
                //生成订单 
                $sql = "insert into orders(sku,create_time) values('$goods_id','$create_time')";
                mysql_query($sql, $conn);
                echo '库存减少成功';
            } else {
                echo '库存减少失败';
            }
        } else {
            echo '库存不够';
        }
    }

}
