<?php
namespace app\controller;
use app\controller\Acl;
use think\facade\{Db,Filesystem};
use think\exception\ValidateException;
class Vrt extends Acl{
    //零售明细表
    public function vlt(){
        $input=input('post.');
        $sheet=['vend','vre','barter'];
        existFull($input,['mold'])||$input['mold']=$sheet;
        if(existFull($input,['page','limit']) && is_arrays($input,['warehouse','mold']) && arrayInArray($input['mold'],$sheet)){
            $sql=[];
            //CLASS语句
            $sql['class']=fastSql($input,[
                ['customer','fullEq'],
                ['number','fullLike'],
                [['startTime'=>'time'],'startTime'],
                [['endTime'=>'time'],'endTime'],
                [['data'=>'class.data'],'fullLike']
            ]);
            $sql['class'][]=['examine','=',1];
            $sql['class']=frameScope($sql['class']);//组织数据
            //数据鉴权[结构一致]
            $sql['class']=sqlAuth('vend',$sql['class']);
            //INFO语句
            $sql['info']=fastSql($input,[['warehouse','fullIn']]);
            //商品匹配
            if(existFull($input,['goods'])){
                $goods=array_column(Db::name('goods')->where([['name|py','like','%'.$input['goods'].'%']])->select()->toArray(),'id');
                $sql['info'][]=['goods','in',$goods];
            }
            //组装查询语句
            $union=[];
            foreach ($input['mold'] as $mold) {
                $union[]=Db::name($mold.'_info')->alias('info')->join(['is_'.$mold=>'class'],'info.pid=class.id')->where($sql['info'])->where($sql['class'])->fieldRaw('info.id as info,class.id as class,time,"'.$mold.'" as mold')->buildSql();
            }
            $union=implode(' UNION ALL ',$union);
             //获取总条数
            $count=DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud')[0]["count"];
            $record=DB::query('SELECT * FROM ('.$union.') as nodcloud ORDER BY `time` DESC LIMIT '.pageCalc($input['page'],$input['limit'],'str'));
            $list=[];
            foreach ($input['mold'] as $mold) {
                $gather=search($record)->where([['mold','=',$mold]])->select();
                $table=[
                    "class"=>"app\\model\\".ucfirst($mold),
                    'info'=>"app\\model\\".ucfirst($mold).'Info',
                ];
                $list[$mold]['info']=$table['info']::with(['goodsData','warehouseData'])->where([['id','in',array_column($gather,'info')]])->select()->toArray();
                $list[$mold]['class']=$table['class']::with(['frameData','customerData'])->where([['id','in',array_column($list[$mold]['info'],'pid')]])->append(['extension'])->select()->toArray();
            }
            $data=[];
            foreach ($record as $recordVo) {
                $mold=$recordVo['mold'];
                $data[]=[
                    'mold'=>$mold,
                    'name'=>['vend'=>'零售单','vre'=>'零售退货单','barter'=>'积分兑换单'][$mold],
                    'class'=>search($list[$mold]['class'])->where([['id','=',$recordVo['class']]])->find(),
                    'info'=>search($list[$mold]['info'])->where([['id','=',$recordVo['info']]])->find()
                ];
            }
            $result=[
                'state'=>'success',
                'count'=>$count,
                'info'=>$data
            ];//返回数据
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //零售明细表-导出
    public function vltExports(){
        $input=input('get.');
        $sheet=['vend','vre','barter'];
        existFull($input,['warehouse'])||$input['warehouse']=[];
        existFull($input,['mold'])||$input['mold']=$sheet;
        if(is_arrays($input,['warehouse','mold']) && arrayInArray($input['mold'],$sheet)){
            pushLog('导出零售明细表');//日志
            $sql=[];
            //CLASS语句
            $sql['class']=fastSql($input,[
                ['customer','fullEq'],
                ['number','fullLike'],
                [['startTime'=>'time'],'startTime'],
                [['endTime'=>'time'],'endTime'],
                [['data'=>'class.data'],'fullLike']
            ]);
            $sql['class'][]=['examine','=',1];
            $sql['class']=frameScope($sql['class']);//组织数据
            //数据鉴权[结构一致]
            $sql['class']=sqlAuth('vend',$sql['class']);
            //INFO语句
            $sql['info']=fastSql($input,[['warehouse','fullDivisionIn']]);
            //商品匹配
            if(existFull($input,['goods'])){
                $goods=array_column(Db::name('goods')->where([['name|py','like','%'.$input['goods'].'%']])->select()->toArray(),'id');
                $sql['info'][]=['goods','in',$goods];
            }
            //组装查询语句
            $union=[];
            foreach ($input['mold'] as $mold) {
                $union[]=Db::name($mold.'_info')->alias('info')->join(['is_'.$mold=>'class'],'info.pid=class.id')->where($sql['info'])->where($sql['class'])->fieldRaw('info.id as info,class.id as class,time,"'.$mold.'" as mold')->buildSql();
            }
            $union=implode(' UNION ALL ',$union);
             //获取总条数
            $record=DB::query('SELECT * FROM ('.$union.') as nodcloud ORDER BY `time` DESC');
            $list=[];
            foreach ($input['mold'] as $mold) {
                $gather=search($record)->where([['mold','=',$mold]])->select();
                $table=[
                    "class"=>"app\\model\\".ucfirst($mold),
                    'info'=>"app\\model\\".ucfirst($mold).'Info',
                ];
                $list[$mold]['info']=$table['info']::with(['goodsData','warehouseData'])->where([['id','in',array_column($gather,'info')]])->select()->toArray();
                $list[$mold]['class']=$table['class']::with(['frameData','customerData'])->where([['id','in',array_column($list[$mold]['info'],'pid')]])->append(['extension'])->select()->toArray();
            }
            $data=[];
            foreach ($record as $recordVo) {
                $mold=$recordVo['mold'];
                $data[]=[
                    'mold'=>$mold,
                    'name'=>['vend'=>'零售单','vre'=>'零售退货单','barter'=>'积分兑换单'][$mold],
                    'class'=>search($list[$mold]['class'])->where([['id','=',$recordVo['class']]])->find(),
                    'info'=>search($list[$mold]['info'])->where([['id','=',$recordVo['info']]])->find()
                ];
            }
            $source=$data;
            //开始构造导出数据
            $excel=[];//初始化导出数据
            //标题数据
            $excel[]=['type'=>'title','info'=>'零售明细表'];
            //表格数据 
            $field=[
                'name'=>'单据类型',
                'class|frameData|name'=>'所属组织',
                'class|customerData|name'=>'客户',
                'class|time'=>'单据时间',
                'class|number'=>'单据编号',
                'info|goodsData|name'=>'商品名称',
                'info|attr'=>'辅助属性',
                'info|warehouseData|name'=>'仓库',
                'info|unit'=>'单位',
                'info|price'=>'单价',
                'info|nums'=>'数量',
                'info|dsc'=>'折扣额',
                'info|total'=>'金额',
                'info|tat'=>'税额',
                'info|tpt'=>'价税合计',
                'class|data'=>'备注信息',
            ];
            $thead=array_values($field);//表格标题
            $tbody=[];//表格内容
            //构造表内数据
            foreach ($source as $sourceVo) {
                $rowData=[];
                foreach (array_keys($field) as $fieldVo) {
                    $rowData[]=arraySeek($sourceVo,$fieldVo);//多键名数据赋值
                }
            	$tbody[]=$rowData;//加入行数据
            }
            $excel[]=['type'=>'table','info'=>['thead'=>$thead,'tbody'=>$tbody]];//表格数据
            //汇总数据
            $math=['vend'=>[],'vre'=>[],'barter'=>[]];
            foreach ($source as $sourceVo) {
                if(in_array($sourceVo['mold'],['vend','vre'])){
                    $math[$sourceVo['mold']][]=$sourceVo['info']['tpt'];
                }else{
                    $math[$sourceVo['mold']][]=$sourceVo['info']['total'];
                }
            }
            $excel[]=['type'=>'node','info'=>[
                '总数:'.count($source),
                '零售总金额:'.mathArraySum($math['vend']),
                '零售退货总金额:'.mathArraySum($math['vre']),
                '积分兑换总金额:'.mathArraySum($math['barter'])
            ]];
            //导出execl
            buildExcel('零售明细表',$excel);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
        
    }
    //零售汇总表
    public function vsy(){
        $input=input('post.');
        if(existFull($input,['page','limit']) && is_array($input['warehouse']) && isset($input['type'])){
            $sql=[];
            //CLASS语句
            $sql['class']=fastSql($input,[
                ['customer','fullEq'],
                ['user','fullEq'],
                ['people','fullEq'],
                [['startTime'=>'time'],'startTime'],
                [['endTime'=>'time'],'endTime']
            ]);
            $sql['class'][]=['examine','=',1];
            $sql['class']=frameScope($sql['class']);//组织数据
            $sql['class']=sqlAuth('vend',$sql['class']);//数据鉴权[结构一致]
            //INFO语句
            $sql['info']=fastSql($input,[['warehouse','fullIn']]);
            //商品匹配
            if(existFull($input,['goods'])){
                $goods=array_column(Db::name('goods')->where([['name|py','like','%'.$input['goods'].'%']])->select()->toArray(),'id');
                $sql['info'][]=['goods','in',$goods];
            }
            //构造语句
            $union=[];
            $tab=['vend','vre','barter'];
            foreach ($tab as $t) {
                $union[]=Db::name($t.'_info')->alias('info')->join(['is_'.$t=>'class'],'info.pid=class.id')->where($sql['class'])->where($sql['info'])->fieldRaw('"'.$t.'" as mold,class.customer as customer,class.user as user,class.people as people,info.id as info,goods,attr,warehouse,unit,nums,'.(in_array($t,['vend','vre'])?'tpt':'info.total as tpt'))->buildSql();
            }
            $union=implode(' UNION ALL ',$union);
            //判断类型
            if($input['type']==0){
                //按商品
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by goods,attr,warehouse  ORDER BY `goods` DESC  LIMIT '.pageCalc($input['page'],$input['limit'],'str'));
            }else if($input['type']==1){
                //按客户
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by customer,goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,customer,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by customer,goods,attr,warehouse ORDER BY `customer` DESC LIMIT '.pageCalc($input['page'],$input['limit'],'str'));
            }else if($input['type']==2){
                //按用户
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by user,goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,user,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by user,goods,attr,warehouse ORDER BY `user` DESC LIMIT '.pageCalc($input['page'],$input['limit'],'str'));
            }else{
                //按人员
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by people,goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,people,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by people,goods,attr,warehouse ORDER BY `people` DESC LIMIT '.pageCalc($input['page'],$input['limit'],'str'));
            }
            //构造数据
            $group = [];
            foreach($record as $vo){
                $moldList = explode(",", $vo['mold']);
                $infoList = explode(",", $vo['info']);
                $unitList = explode(",", $vo['unit']);
                $numsList = explode(",", $vo['nums']);
                $tptList = explode(",", $vo['tpt']);
                $row=['mold'=>$moldList[0],'info'=>$infoList[0]];
                foreach ($moldList as $key => $mold) {
                    $row[$mold]['unit'][]=$unitList[$key];
                    $row[$mold]['nums'][]=$numsList[$key];
                    $row[$mold]['tpt'][]=$tptList[$key];
                }
                $input['type']==1&&$row['customer']=$vo['customer'];//客户转存
                $input['type']==2&&$row['user']=$vo['user'];//用户转存
                $input['type']==3&&$row['people']=$vo['people'];//人员转存
                $group[]=$row;
            }
            //数据匹配
            $infoList=[];
            foreach ($tab as $t) {
                $mold="app\\model\\".ucfirst($t).'Info';
                $gather=search($group)->where([['mold','=',$t]])->select();
                $infoList[$t]=$mold::with(['goodsData','warehouseData'])->where([['id','in',array_column($gather,'info')]])->select()->toArray();
            }
            //查询类型-匹配客户
            $input['type']==1&&$customerList=db::name('customer')->where([['id','in',array_column($group,'customer')]])->select()->toArray();
            //查询类型-匹配用户
            $input['type']==2&&$userList=db::name('user')->where([['id','in',array_column($group,'user')]])->select()->toArray();
            //查询类型-匹配人员
            $input['type']==3&&$peopleList=db::name('people')->where([['id','in',array_column($group,'people')]])->select()->toArray();
            //数据处理
            $data=[];
            foreach ($group as $groupVo) {
                $row=search($infoList[$groupVo['mold']])->where([['id','=',$groupVo['info']]])->find();
                $row['unit']=$row['goodsData']['unit']==-1?'多单位':$row['unit'];
                foreach ($tab as $t) {
                    if(isset($groupVo[$t])){
                        if($row['goodsData']['unit']==-1){
                            $base=0;
                            foreach ($groupVo[$t]['unit'] as $key=> $unit) {
                                $radix=unitRadix($unit,$row['goodsData']['units']);
                                $base=math()->chain($groupVo[$t]['nums'][$key])->mul($radix)->add($base)->done();
                            }
                            $row[$t]=['base'=>$base,'nums'=>unitSwitch($base,$row['goodsData']['units']),'money'=>mathArraySum($groupVo[$t]['tpt'])];
                            $row[$t]['price']=math()->chain($row[$t]['money'])->div($base)->round(2)->done();
                        }else{
                            $row[$t]=['nums'=>mathArraySum($groupVo[$t]['nums']),'money'=>mathArraySum($groupVo[$t]['tpt'])];
                            $row[$t]['price']=math()->chain($row[$t]['money'])->div($row[$t]['nums'])->round(2)->done();
                            $row[$t]['base']=$row[$t]['nums'];
                        }
                    }else{
                        $row[$t]=['base'=>0,'price'=>0,'nums'=>0,'money'=>0];
                    }
                }
                $row['summary']['nums']=math()->chain($row['vend']['base'])->sub($row['vre']['base'])->add($row['barter']['base'])->done();
                $row['goodsData']['unit']==-1&&$row['summary']['nums']=unitSwitch($row['summary']['nums'],$row['goodsData']['units']);
                $row['summary']['money']=math()->chain($row['vend']['money'])->sub($row['vre']['money'])->add($row['barter']['money'])->done();
                //类型匹配
                $input['type']==1&&$row['customer']=search($customerList)->where([['id','=',$groupVo['customer']]])->find();//匹配客户
                $input['type']==2&&$row['user']=search($userList)->where([['id','=',$groupVo['user']]])->find();//匹配用户
                $input['type']==3&&$row['people']=search($peopleList)->where([['id','=',$groupVo['people']]])->find();//匹配人员
                $data[]=$row;
            }
            $result=[
                'state'=>'success',
                'count'=>$count,
                'info'=>$data
            ];//返回数据
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //零售汇总表-导出
    public function vsyExports(){
        $input=input('get.');
        existFull($input,['warehouse'])||$input['warehouse']=[];
        if(is_array($input['warehouse']) && isset($input['type'])){
            pushLog('导出零售汇总表');//日志
            $sql=[];
            //CLASS语句
            $sql['class']=fastSql($input,[
                ['customer','fullEq'],
                ['user','fullEq'],
                ['people','fullEq'],
                [['startTime'=>'time'],'startTime'],
                [['endTime'=>'time'],'endTime']
            ]);
            $sql['class'][]=['examine','=',1];
            $sql['class']=frameScope($sql['class']);//组织数据
            $sql['class']=sqlAuth('vend',$sql['class']);//数据鉴权[结构一致]
            //INFO语句
            $sql['info']=fastSql($input,[['warehouse','fullDivisionIn']]);
            //商品匹配
            if(existFull($input,['goods'])){
                $goods=array_column(Db::name('goods')->where([['name|py','like','%'.$input['goods'].'%']])->select()->toArray(),'id');
                $sql['info'][]=['goods','in',$goods];
            }
            //构造语句
            $union=[];
            $tab=['vend','vre','barter'];
            foreach ($tab as $t) {
                $union[]=Db::name($t.'_info')->alias('info')->join(['is_'.$t=>'class'],'info.pid=class.id')->where($sql['class'])->where($sql['info'])->fieldRaw('"'.$t.'" as mold,class.customer as customer,class.user as user,class.people as people,info.id as info,goods,attr,warehouse,unit,nums,'.(in_array($t,['vend','vre'])?'tpt':'info.total as tpt'))->buildSql();
            }
            $union=implode(' UNION ALL ',$union);
            //判断类型
            if($input['type']==0){
                //按商品
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by goods,attr,warehouse  ORDER BY `goods` DESC');
            }else if($input['type']==1){
                //按客户
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by customer,goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,customer,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by customer,goods,attr,warehouse ORDER BY `customer` DESC');
            }else if($input['type']==2){
                //按用户
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by user,goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,user,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by user,goods,attr,warehouse ORDER BY `user` DESC');
            }else{
                //按人员
                $count=count(DB::query('SELECT COUNT(*) as count FROM ('.$union.') as nodcloud group by people,goods,attr,warehouse'));
                $record = DB::query('SELECT group_concat(mold) as mold,people,group_concat(info) as info,group_concat(unit) as unit,group_concat(nums) as nums,group_concat(tpt) as tpt FROM ( '.$union.' ) as nodcloud group by people,goods,attr,warehouse ORDER BY `people` DESC');
            }
            //构造数据
            $group = [];
            foreach($record as $vo){
                $moldList = explode(",", $vo['mold']);
                $infoList = explode(",", $vo['info']);
                $unitList = explode(",", $vo['unit']);
                $numsList = explode(",", $vo['nums']);
                $tptList = explode(",", $vo['tpt']);
                $row=['mold'=>$moldList[0],'info'=>$infoList[0]];
                foreach ($moldList as $key => $mold) {
                    $row[$mold]['unit'][]=$unitList[$key];
                    $row[$mold]['nums'][]=$numsList[$key];
                    $row[$mold]['tpt'][]=$tptList[$key];
                }
                $input['type']==1&&$row['customer']=$vo['customer'];//客户转存
                $input['type']==2&&$row['user']=$vo['user'];//用户转存
                $input['type']==3&&$row['people']=$vo['people'];//人员转存
                $group[]=$row;
            }
            //数据匹配
            $infoList=[];
            foreach ($tab as $t) {
                $mold="app\\model\\".ucfirst($t).'Info';
                $gather=search($group)->where([['mold','=',$t]])->select();
                $infoList[$t]=$mold::with(['goodsData','warehouseData'])->where([['id','in',array_column($gather,'info')]])->select()->toArray();
            }
            //查询类型-匹配客户
            $input['type']==1&&$customerList=db::name('customer')->where([['id','in',array_column($group,'customer')]])->select()->toArray();
            //查询类型-匹配用户
            $input['type']==2&&$userList=db::name('user')->where([['id','in',array_column($group,'user')]])->select()->toArray();
            //查询类型-匹配人员
            $input['type']==3&&$peopleList=db::name('people')->where([['id','in',array_column($group,'people')]])->select()->toArray();
            //数据处理
            $data=[];
            foreach ($group as $groupVo) {
                $row=search($infoList[$groupVo['mold']])->where([['id','=',$groupVo['info']]])->find();
                $row['unit']=$row['goodsData']['unit']==-1?'多单位':$row['unit'];
                foreach ($tab as $t) {
                    if(isset($groupVo[$t])){
                        if($row['goodsData']['unit']==-1){
                            $base=0;
                            foreach ($groupVo[$t]['unit'] as $key=> $unit) {
                                $radix=unitRadix($unit,$row['goodsData']['units']);
                                $base=math()->chain($groupVo[$t]['nums'][$key])->mul($radix)->add($base)->done();
                            }
                            $row[$t]=['base'=>$base,'nums'=>unitSwitch($base,$row['goodsData']['units']),'money'=>mathArraySum($groupVo[$t]['tpt'])];
                            $row[$t]['price']=math()->chain($row[$t]['money'])->div($base)->round(2)->done();
                        }else{
                            $row[$t]=['nums'=>mathArraySum($groupVo[$t]['nums']),'money'=>mathArraySum($groupVo[$t]['tpt'])];
                            $row[$t]['price']=math()->chain($row[$t]['money'])->div($row[$t]['nums'])->round(2)->done();
                            $row[$t]['base']=$row[$t]['nums'];
                        }
                    }else{
                        $row[$t]=['base'=>0,'price'=>0,'nums'=>0,'money'=>0];
                    }
                }
                $row['summary']['nums']=math()->chain($row['vend']['base'])->sub($row['vre']['base'])->add($row['barter']['base'])->done();
                $row['goodsData']['unit']==-1&&$row['summary']['nums']=unitSwitch($row['summary']['nums'],$row['goodsData']['units']);
                $row['summary']['money']=math()->chain($row['vend']['money'])->sub($row['vre']['money'])->add($row['barter']['money'])->done();
                //类型匹配
                $input['type']==1&&$row['customer']=search($customerList)->where([['id','=',$groupVo['customer']]])->find();//匹配客户
                $input['type']==2&&$row['user']=search($userList)->where([['id','=',$groupVo['user']]])->find();//匹配用户
                $input['type']==3&&$row['people']=search($peopleList)->where([['id','=',$groupVo['people']]])->find();//匹配人员
                $data[]=$row;
            }
            $source=$data;
            //开始构造导出数据
            $excel=[];//初始化导出数据
            //标题数据
            $excel[]=['type'=>'title','info'=>'零售汇总表'];
            //表格数据
            $field=[
                [
                    "customer|name"=>"客户"
                ],
                [
                    "user|name"=>"用户"
                ],
                [
                    "people|name"=>"关联人员"
                ],
                [
                    "goodsData|name"=>"商品名称",
                    "attr"=>"辅助属性",
                    "warehouseData|name"=>"仓库",
                    "unit"=>"单位",
                    "vend|price"=>"零售单价",
                    "vend|nums"=>"零售数量",
                    "vend|money"=>"零售金额",
                    "vre|price"=>"购退单价",
                    "vre|nums"=>"购退数量",
                    "vre|money"=>"购退金额",
                    "barter|price"=>"积兑单价",
                    "barter|nums"=>"积兑数量",
                    "barter|money"=>"积兑金额",
                    "summary|nums"=>"汇总数量",
                    "summary|money"=>"汇总金额"
                ]
            ];
            $field=[$field[3],array_merge($field[0],$field[3]),array_merge($field[1],$field[3]),array_merge($field[2],$field[3]),][$input['type']];
            $thead=array_values($field);//表格标题
            $tbody=[];//表格内容
            //构造表内数据
            foreach ($source as $sourceVo) {
                $rowData=[];
                foreach (array_keys($field) as $fieldVo) {
                    $rowData[]=arraySeek($sourceVo,$fieldVo);//多键名数据赋值
                }
            	$tbody[]=$rowData;//加入行数据
            }
            $excel[]=['type'=>'table','info'=>['thead'=>$thead,'tbody'=>$tbody]];//表格数据
            //汇总数据
            $excel[]=['type'=>'node','info'=>[
                '总数:'.count($source),
                '零售总金额:'.mathArraySum(arrayColumns($source,['vend','money'])),
                '零售退货总金额:'.mathArraySum(arrayColumns($source,['vre','money'])),
                '积分兑换总金额:'.mathArraySum(arrayColumns($source,['barter','money'])),
                '汇总金额:'.mathArraySum(arrayColumns($source,['summary','money']))
            ]];
            //导出execl
            buildExcel('零售汇总表',$excel);
        }else{
            return json(['state'=>'error','info'=>'传入参数不完整!']);
        }
    }
}
