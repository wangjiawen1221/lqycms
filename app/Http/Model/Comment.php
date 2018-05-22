<?php
namespace App\Http\Model;
use DB;
use Log;

class Comment extends BaseModel
{
	//评价
	
    protected $table = 'comment';
	public $timestamps = false;
	
	/**
     * 不能被批量赋值的属性
     *
     * @var array
     */
    protected $guarded = [];
	
    const UNSHOW_COMMENT = 0; //评论未批准显示
    const SHOW_COMMENT = 1; //评论批准显示
    const GOODS_COMMENT_TYPE = 0; //商品评论
    const ARTICLE_COMMENT_TYPE = 1; //文章评论
    
    
    public function getDb()
    {
        return DB::table($this->table);
    }
    
    /**
     * 列表
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $offset 偏移量
     * @param int $limit 取多少条
     * @return array
     */
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 10)
    {
        $model = $this->getDb();
        if($where){$model = $model->where($where);}
        
        $res['count'] = $model->count();
        $res['list'] = array();
        
        if($res['count'] > 0)
        {
            if($field){if(is_array($field)){$model = $model->select($field);}else{$model = $model->select(\DB::raw($field));}}
            if($order){$model = parent::getOrderByData($model, $order);}
            if($offset){}else{$offset = 0;}
            if($limit){}else{$limit = 10;}
            
            $res['list'] = $model->skip($offset)->take($limit)->get();
        }
        
        return $res;
    }
    
    /**
     * 分页，用于前端html输出
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 每页几条
     * @param int $page 当前第几页
     * @return array
     */
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 10)
    {
        $res = $this->getDb();
        
        if($where){$res = $res->where($where);}
        if($field){if(is_array($field)){$res = $res->select($field);}else{$res = $res->select(\DB::raw($field));}}
        if($order){$res = parent::getOrderByData($res, $order);}
        if($limit){}else{$limit = 10;}
        
        return $res->paginate($limit);
    }
    
    /**
     * 查询全部
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 取多少条
     * @return array
     */
    public function getAll($where = array(), $order = '', $field = '*', $limit = 10, $offset = 0)
    {
        $res = $this->getDb();
        
        if($where){$res = $res->where($where);}
        if($field){if(is_array($field)){$res = $res->select($field);}else{$res = $res->select(\DB::raw($field));}}
        if($order){$res = parent::getOrderByData($res, $order);}
        if($offset){}else{$offset = 0;}
        if($limit){}else{$limit = 10;}
        
        $res = $res->skip($offset)->take($limit)->get();
        
        return $res;
    }
    
    /**
     * 获取一条
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getOne($where, $field = '*')
    {
        $res = $this->getDb();
        
        if($where){$res = $res->where($where);}
        if($field){if(is_array($field)){$res = $res->select($field);}else{$res = $res->select(\DB::raw($field));}}
        
        $res = $res->first();
        
        return $res;
    }
    
    /**
     * 添加
     * @param array $data 数据
     * @return int
     */
    public function add(array $data,$type = 0)
    {
        if($type==0)
        {
            // 新增单条数据并返回主键值
            return self::insertGetId(parent::filterTableColumn($data,$this->table));
        }
        elseif($type==1)
        {
            /**
             * 添加单条数据
             * $data = ['foo' => 'bar', 'bar' => 'foo'];
             * 添加多条数据
             * $data = [
             *     ['foo' => 'bar', 'bar' => 'foo'],
             *     ['foo' => 'bar1', 'bar' => 'foo1'],
             *     ['foo' => 'bar2', 'bar' => 'foo2']
             * ];
             */
            return self::insert($data);
        }
    }
    
    /**
     * 修改
     * @param array $data 数据
     * @param array $where 条件
     * @return bool
     */
    public function edit($data, $where = array())
    {
        $res = $this->getDb();
        $res = $res->where($where)->update(parent::filterTableColumn($data, $this->table));
        
        if ($res === false)
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * 删除
     * @param array $where 条件
     * @return bool
     */
    public function del($where)
    {
        $res = $this->getDb();
        $res = $res->where($where)->delete();
        
        return $res;
    }
    /* 
    //获取列表
	public static function getList(array $param)
    {
        extract($param); //参数：limit，offset
        
        $where['user_id'] = $user_id;
        $where['status'] = self::SHOW_COMMENT;
        
        $limit  = isset($limit) ? $limit : 10;
        $offset = isset($offset) ? $offset : 0;
        
        $model = new self;
        
        if(isset($comment_rank)){$where['comment_rank'] = $comment_rank;} //评价分
        if(isset($id_value)){$where['id_value'] = $id_value;} //商品的id
        if(isset($comment_type)){$where['comment_type'] = $comment_type;} //0商品评价，1文章评价
        if(isset($parent_id)){$where['parent_id'] = $parent_id;}
        
        $model = $model->where($where);
        
        $res['count'] = $model->count();
        $res['list'] = array();
        
		if($res['count']>0)
        {
            $res['list']  = $model->skip($offset)->take($limit)->orderBy('id','desc')->get();
        }
        
        return $res;
    }
    
    public static function getOne($where)
    {
        return self::where($where)->first();
    }
    
    public static function add(array $data)
    {
        if(!isset($data['comment_type']) || !isset($data['id_value']) || !isset($data['user_id']) || $data['comment_type']===null || $data['id_value']===null || $data['user_id']===null)
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        
        if(!isset($data['content']) && !isset($data['comment_rank']))
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        else
        {
            if($data['content']===null && $data['comment_rank']===null)
            {
                return ReturnData::create(ReturnData::PARAMS_ERROR);
            }
        }
        
        if(self::where(array('user_id'=>$data['user_id'],'id_value'=>$data['id_value'],'comment_type'=>$data['comment_type']))->first()){return ReturnData::create(ReturnData::SYSTEM_FAIL,null,'亲，您已经评价啦！');}
        
        if ($id = self::insertGetId($data))
        {
            return ReturnData::create(ReturnData::SUCCESS,$id);
        }
        
        return ReturnData::create(ReturnData::SYSTEM_FAIL);
    }
    
    //批量添加
    public static function batchAdd(array $data)
    {
        $res = '';
        
        if($data)
        {
            foreach($data as $k=>$v)
            {
                $id = self::add($v);
                if($id['code']==0){$res[] = $id['data'];}else{return $id;}
            }
            
            return ReturnData::create(ReturnData::SUCCESS,$res);
        }
        
        return ReturnData::create(ReturnData::SYSTEM_FAIL);
    }
    
    public static function modify($where, array $data)
    {
        if (self::where($where)->update($data))
        {
            return true;
        }
        
        return false;
    }
    
    //删除一条记录
    public static function remove(array $data)
    {
        if(!self::where(array('user_id'=>$data['user_id'],'comment_type'=>$data['comment_type'],'id_value'=>$data['id_value']))->first()){return '商品尚未评价';}
        
        if (self::where(array('user_id'=>$data['user_id'],'comment_type'=>$data['comment_type'],'id_value'=>$data['id_value']))->delete() === false)
        {
            return false;
        }
        
        return true;
    } */
}