<?php
namespace app\index\controller;
use ORG\Rediscache;

class Test
{
    
    public function aa() {
//         $this->logs("恭喜,获得抢购名额!\r\n");exit;
//        $Rediscache=new Rediscache("127.0.0.1", "6379", "fanao123!@#");
          $Rediscache=new Rediscache("39.99.209.62", "6379", "redis123456");
          //模拟高并发下进行活动商品抢购
          
          //《1》string类型 设置跟取值,删除
//          $Rediscache->set("study.one", "one");
//          $Rediscache->set("study.two", "two");
//          echo $Rediscache->get("study.two");
//          $Rediscache->del("study.two"); 
          
          //《2》sets集合类型
          //sets集合添加元素
//          $Rediscache->sadd("studys.set1", "set1");
//          $Rediscache->sadd("studys.set1", "set2");
//          $Rediscache->sadd("studys.set1", "set3");
//          //查询sets集合所有元素
//          var_dump($Rediscache->smembers("studys.set1")); 
//          //删除sets集合里面的某个元素
//          $Rediscache->srem("studys.set1", "set2");
          
          //lists链表类型  活动商品抢购
          //设置库存100件
//          for ($index = 0; $index < 100; $index++) {
//              $Rediscache->lpush("goods_stock","1"); 
//          } 
//          echo $Rediscache->lLen("goods_stock");exit;
          
           if($Rediscache->lLen("goods_stock")<=0){ 
               $this->logs("很抱歉,活动商品已抢空!\r\n");
           }else{
               //记录有名额的用户信息到队列member
               $Rediscache->lpush("member_1","1");
               //商品库存减1
               $Rediscache->lpop("goods_stock"); 
               $this->logs("恭喜,获得抢购名额!\r\n");
           }
           
    }
    
    
    public function logs($data) {
       file_put_contents( "_request_log.txt", $data,FILE_APPEND);
    }
 
    
    public function aaa()
    { 
        
         if(isset($_GET['one'])){
             $parm=$_GET['one'];
         }else{
             echo '参数one不能为空';exit;
         }
       
         if(isset($_GET['two'])){
             $parm2=$_GET['two'];
         }else{
             echo '参数two不能为空';exit;
         } 
        //玩一个随机数，然后两个随机数相加小于500输出
        $one=  rand(1, 99);
        $two=  rand(100, 999);
        echo '输出'.$parm.'和'.$parm2;
        if($one+$two>=500){
            echo '大于500';
        }else{
            echo '小于500';
        }
    }
}
