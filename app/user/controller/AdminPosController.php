<?php

namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\user\model\PosModel;


class AdminPosController extends AdminBaseController
{

    public function index()
    {
        $where   = [];
        $request = input('request.');

        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }
        if(!empty($request['status'])){
            $where['status'] = intval($request['status']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['code']    = ['like', "%$keyword%"];
        }
        $q_pos = Db::name('pos');

        $list = $q_pos->where($where)->whereOr($keywordComplex)->order("status DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

   public function add()
   {
        return $this->fetch();
   }

   public function doAdd()
   {
        $data = $_POST;
        if(empty($data))
        {
            $this->error("数据为空！");
        }
        $q_pos = Db::name('pos');
        if(empty($data['end_code']))
        {
            if(strlen($data['begin_code']) != 16){
                $this->error("起始号格式不符");
            }
            $res = $q_pos->where('code',$data['begin_code'])->find();
            if($res){
                $this->error("机具号已经存在");
            }else{
                $data_i['code'] = $data['begin_code'];
                $q_pos->insert($data_i);
                $this->success("成功添加一条");
            }
        }else{
            $b_code = $_POST['begin_code'];
            $e_code = $_POST['end_code'];
            if(strlen($b_code) != strlen($e_code) || strlen($b_code) != 16){
                $this->error("起始号或结束号格式不符");
            }else{
                $b_pre = substr($b_code,0,-4);
                $e_pre = substr($e_code,0,-4);
                if($b_pre != $e_pre){
                    $this->error("两次机具号格式不相等");
                }
                $b_num = substr($b_code,-4,4);
                $e_num = substr($e_code,-4,4);
                $num = $e_num - $b_num +1;
                if($num <= 0){
                    $this->error('结束号必须大于起始号');
                }elseif($num >999){
                    $this->error("单次添加不能超过999台");
                }else{
                    
                    for($nex=$b_num;$nex<=$e_num;$nex++){
                        $code = $b_pre.$nex;
                        $res = $q_pos->where('code',$code)->find();
                        if($res){
                            $this->error($code."机具号已经存在,中断添加");
                        }else{
                            $data_i['code'] = $code;
                            $q_pos->insert($data_i);
                        }
                    }
                    $this->success("成功添加".$num."条");
                }
              
            }
        }
   }

   public function doDel()
   {
        $id = input('param.id', 0, 'intval');
        if($id){
            $q_pos = Db::name('pos');
            $q_pos->delete($id);
            $this->success("删除成功");
        }else{
            $this->error("参数错误");
        }
   }

   public function doDetail()
   {
        $data = $_POST;
        if(!empty($data)){
            Db::name('pos')->where('id',$data['id'])->setField("detail",$data['detail']);
            $this->success("备注成功");
        }else{
            $this->error("参数错误");
        }
   }

   public function doBind()
   {
        $data = $_POST;
        
        if($data['id'] > 0 && $data['uid'] > 0 ){
            if(!Db::name('user')->find($data['uid'])){
                $this->error('用户不存在');
            }
            // if(Db::name('pos')->where('uid',$data['uid'])->find()){
            //     $this->error('一个用户只能绑定一台POS');
            // }
            $m_pos = new PosModel();
            $ret = $m_pos->doBind($data['id'],$data['uid']);
            if($ret > 0){
                $this->success("绑定成功");
            }
        }else{
            $this->error("参数错误");
        }
   }

   public function doUnbind()
   {
        $data['id'] = input('param.id', 0, 'intval');
        if($data['id'] > 0){
            $m_pos = new PosModel();
            $ret = $m_pos->doUnbind($data['id']);
            if($ret > 0){
                $this->success("解绑完成");
            }
        }else{
            $this->error("参数错误");
        }
   }

   public function doActive()
   {
        $id = input('param.id', 0, 'intval');
        if($id > 0){
            $m_pos = new PosModel();
            $ret = $m_pos->doActive($id,true);
            if($ret > 0){
                $this->success('激活完成');
            }else{
                $this->error('激活失败');
            }
        }else{
            $this->error('参数错误');
        }
   }

   public function posRequest(){
        $list = Db::name('pos_request')->order('status, time DESC')->paginate(10);
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
   }

   public function doDelRequest()
   {
        $id = input('param.id', 0, 'intval');
        if($id){
            $q_pos = Db::name('pos_request');
            $q_pos->delete($id);
            $this->success("删除成功");
        }else{
            $this->error("参数错误");
        }
   }

   public function doPassRequest()
   {
        $id = input('param.id', 0, 'intval');
        if(!$id){
            $this->error("参数错误");
        }

        $p_r_data = Db::name('pos_request')->find($id);

        
       
        if(Db::name('pos')->where(['code'=>$p_r_data['code'],'status'=>2 ])->find()){
            $this->error('此机具号已经激活过');
        }

        if(Db::name('pos')->where(['code'=>$p_r_data['code'],'status'=>1 ])->find() && $p_r_data['type'] == 'bind'){
            $this->error('此机具号已经绑定过');
        }

        $ret = Db::name('pos_request')->where('id',$id)->setField('status',1);
        if($p_r_data['type'] == 'bind'){
            $pos_data = [
                'uid' => $p_r_data['uid'],
                'status' => 1,
            ];
            $ret = Db::name('pos')->where('code',$p_r_data['code'])->update($pos_data);
            if($ret > 0){
                $this->success("绑定成功");
            }else{
                $this->error("绑定失败");
            } 
        }
        if($p_r_data['type'] == 'active'){
            $pos_id = Db::name('pos')->where('code',$p_r_data['code'])->value('id');
            $m_pos = new PosModel();
            $ret = $m_pos->doActive($pos_id,true);
            if($ret > 0){
                $this->success("激活成功");
            }else{
                $this->error("激活失败");
            }  
        }
   }
}
