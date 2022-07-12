<?php

namespace app\admin\controller\notice;

use addons\notice\library\NoticeClient;
use app\admin\model\notice\NoticeEvent;
use app\admin\model\notice\NoticeTemplate;
use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 消息模版
 *
 * @icon fa fa-circle-o
 */
class Template extends Backend
{
    
    /**
     * NoticeTemplate模型对象
     * @var \app\admin\model\notice\NoticeTemplate
     */
    protected $model = null;

    protected $noNeedRight = ['visible'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\notice\NoticeTemplate;
        $this->view->assign("platformList", NoticeClient::instance()->getPlatformList());
        $this->view->assign("typeList", NoticeClient::instance()->getTypeList());
        $this->view->assign("visibleSwitchList", $this->model->getVisibleSwitchList());
    }


    public function add()
    {
        return false;
    }


    public function edit($ids = null)
    {
        $where = $this->request->only(['notice_event_id', 'platform', 'type']);
        if (count($where) != 3) {
            return_error('参数错误');
        }

        $event = NoticeEvent::get($where['notice_event_id']);
        $this->assign('event', $event);
        row_check($event);
        $row = $this->model->get($where);
        if (!$row) {
            $row = $this->model;
            $row->notice_event_id = $where['notice_event_id'];
            $row->platform = $where['platform'];
            $row->type = $where['type'];
            $row->content = '';
            $row->visible_switch = 1;
            $row->mptemplate_id = '';
            $row->mptemplate_json = '';
            $row->url_type = 1;
            $row->url_title = '';
            $row->url = '';
        }

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if (isset($params['mptemplate_id'])) {
                $params['mptemplate_id'] = trim($params['mptemplate_id']);
            }
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);

        $this->view->assign('urlTypeList', $row->getUrlTypeList());
        return $this->view->fetch();
    }


    /**
     * 查看
     */
    public function index()
    {
        // 依据通知事件更新模板表
        $eventList = NoticeEvent::scope('frontend')->select();
        $templateList = [];
        $noticeClient = new NoticeClient();

        $default_params = [
            // 默认平台
            'platform' => array_keys($noticeClient->getPlatformData())[0],
        ];
        $params = $this->request->only(['platform']);
        $params = array_merge($default_params, $params);

        // 当前平台支持的类型
        $typeList = $noticeClient->getPlatformData()[$params['platform']]['type'] ?? [];
        $typeList = array_combine($typeList, $typeList);
        foreach ($typeList as $k=>$v) {
            $typeList[$k] = $noticeClient->getTypeText($k);
        }

        foreach ($eventList as $item) {
            $platformArr = explode(',', $item['platform']);
            $typeArr = explode(',', $item['type']);
            foreach ($platformArr as $v) {
                if ($v != $params['platform']) {
                    continue;
                }
                $templateItem = [
                    'noticeevent' => $item,
                    'item' => []
                ];
                foreach ($typeList as $k2 => $v2) {
                    // 判断是否支持
                    $is = in_array($k2, $typeArr);
                    if ($is) {
                        $_item = [
                            'notice_event_id' => $item['id'],
                            'platform' => $v,
                            'type' => $k2,
                            'type_text' => $v2,
                            'content' => null,
                            'visible_switch' => 0,
                            'id' => 0,
                            'send_num' => '-',
                            'send_fail_num' => '-',
                            'error' => false
                        ];
                        // 判断是否有记录
                        $template = $noticeClient->getTemplateByPlatformAndType($item['id'],$v, $k2);
                        if ($template) {
                            $_item = array_merge($_item, $template->toArray());
                        }
                        $templateItem['item'][] = $_item;
                    } else {
                        $templateItem['item'][] = [
                            'error' => '不支持'
                        ];
                    }
                }
                if ($templateItem['item']) {
                    $templateList[] = $templateItem;
                }
            }
        }

        $list = $templateList;
        $this->assign('list', $list);
        $this->assign('typeList', $typeList);
        $this->assign('params', $params);
        return $this->view->fetch();
    }


    /**
     * 开关
     */
    public function visible()
    {
        $params = $this->request->only(['notice_event_id', 'platform', 'type', 'visible_switch']);
        if (count($params) != 4) {
            return_error('缺少参数');
        }
        $where = $params;
        unset($where['visible_switch']);
        $row = $this->model->where($where)->find();
        if (!$row) {
            $this->error('请先配置');
        }
        $row['visible_switch'] = $params['visible_switch'];
        $row->save();
        $this->success('操作成功');
    }
}
