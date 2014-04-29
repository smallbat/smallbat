<?php
class BlogAction extends CommonAction {

    public function index() {
	cccccccc

    }


    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 博客设置
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */


    // 基本设置
    public function basic() {
        $Blog = D('Blog');

        if ( $_POST['submit'] ) {

            if( $Blog->create() ) {

                if ( $Blog->blog_id ) {

                    if( $Blog->save() ) {

                        /*判断图片是否存在，不存在，则删除数据库中的记录*/
                        if( $Blog->wx_ewm && !file_exists($Blog->wx_ewm) ) {
                            $Blog->wx_ewm = "";
                        }

                        $this->success('数据保存成功！', appUrl('Blog/basic'));
                    } else {
                        $this->error('数据保存失败！');
                    }

                } else {

                    $Blog->uid = $_SESSION['_User']['uid'];
                    if( $Blog->add() ) {
                        $this->success('数据添加成功！', appUrl('Blog/basic'));
                    } else {
                        $this->error('数据添加失败！');
                    }
                }

            } else {
                $this->error('数据添加失败！');
            }

        } else {

            // 获取站点基本信息
            $map['uid'] = $_SESSION['_User']['uid'];
            $obj = $Blog->where($map)->find();
            $this->assign('obj', $obj);

            $this->display('basic');
        }


    }



    // 导航管理
    public function nav() {
        $BlogNav = D('BlogNav');
        $view = $_GET['view'] ? $_GET['view'] : 'list';

        // 获取 工具栏
        $this->nav_tool();

        switch ( $view ) {
            case "list":

                if ( $_POST['submit'] ) {

                    // 工具栏操作
                    $tool = $_POST['tool'];
                    $navids = $_POST['nav_id'];

                    if ( $tool && !count($navids) ) {
                        $this->error('请选择要操作的选项！');
                        exit();
                    }


                    $sUrl = $_POST['position'] ?  'Blog/nav/position/'.$_POST['position'] :  'Blog/nav';
                    switch( $tool ) {
                        case "delAll":

                            foreach( $navids as $k=>$v ) {
                                $map['nav_id'] = $k;
                                $map['uid'] = $_SESSION['_User']['uid'];
                                $BlogNav->where($map)->delete();
                            }

                            $this->success('数据删除成功！', appUrl($sUrl));
                            exit();
                            break;
                    }


                    $nav = $_POST['nav'];
                    $result = false;
                    foreach( $nav as $i => $k ) {

                        $map['nav_id'] = $i;
                        $data['listorder'] = $k['listorder'];

                        if( $BlogNav->where($map)->save($data) ) {
                            $result = true;
                        }
                    }

                    if( $result ) {
                        $this->success('数据保存成功！', appUrl($sUrl));
                    } else {
                        $this->error('数据保存失败！');
                    }

                } else {



                    // 获取 Blog导航信息
                    $map['uid'] = $_SESSION['_User']['uid'];
                    $map['nav_position'] = $_GET['position'] ? $_GET['position'] : 'nav';
                    //var_dump($map);
                    $order['listorder'] = 'asc';
                    $list = $BlogNav->where($map)->order($order)->select();
                    //var_dump($list);
                    $this->assign('list', $list);

                    $this->display('nav');
                }

                break;

            case "custom":

                if ( $_POST['submit'] ) {

                    $sUrl = $_POST['position'] ? 'Blog/nav/position/'.$_POST['position'].'/view/custom' :  'Blog/nav/view/custom';
                    if($BlogNav->create()){

                        $BlogNav->data_format();

                        if( $BlogNav->nav_id) {

                            if( $BlogNav->save() ) {
                                $this->success('数据保存成功！', appUrl( $sUrl.'/nav_id/'.$_POST['nav_id']));
                            } else {
                                $this->error('数据保存失败！');
                            }
                        }else{

                            $BlogNav->uid = $_SESSION['_User']['uid'];
                            $BlogNav->nav_type = 'custom';
                            if( $BlogNav->add() ) {
                                $this->success('数据添加成功！', appUrl( $sUrl ));
                            } else {
                                $this->error('数据添加失败！');
                            }

                        }
                    } else {
                        $this->error('数据添加失败！');
                    }

                } else {

                    $map['nav_id'] = $_GET['nav_id'];
                    $map['uid'] = $_SESSION['_User']['uid'];
                    $map['nav_position'] = $_GET['position'] ? $_GET['position'] : 'nav';
                    $obj = $BlogNav->where($map)->find();
                    $this->assign('obj', $obj);

                    $this->display('nav_custom');
                }

                break;

            case "cate":

                if ( $_POST['submit'] ) {

                    $sUrl = $_POST['position'] ? 'Blog/nav/position/'.$_POST['position'] : 'Blog/nav';

                    $cate = $_POST['cate'];
                    $result = false;
                    foreach( $cate as $i => $k ) {

                        $data['nav_name'] = $k['catname'];
                        $data['uid'] = $_SESSION['_User']['uid'];
                        $data['is_show'] = 0;
                        $data['linkurl'] = C('WEB_URL_BLOG').'/Category/lists/uid/'.$_SESSION['_User']['uid'].'/catid/'.$i;
                        $data['nav_type'] = 'cate';
                        $data['nav_position'] = $_POST['nav_position'];

                        if ($BlogNav->add($data)) {
                            $result = true;
                        }
                    }

                    if( $result ) {
                        $this->success('数据添加成功！', appUrl( $sUrl ));
                    } else {
                        $this->error('数据添加失败！');
                    }

                } else {

                    // 文章分类获取
                    $ArticleCategory = D('ArticleCategory');

                    $map['is_sys'] = 1;
                    $map['_query'] = 'uid='.$_SESSION['_User']['uid'].'&is_sys=0';
                    $map['_logic'] = 'or';
                    $cateList = $ArticleCategory->where($map)->order('listorder')->select();
                    $this->assign('cateList', $cateList);

                    $this->display('nav_cate');
                }
                break;

            case "webpage":

                if ( $_POST['submit'] ) {

                    $sUrl = $_POST['position'] ?  'Blog/nav/position/'.$_POST['position'] : 'Blog/nav';

                    $webPage = $_POST['webPage'];
                    $result = false;
                    foreach( $webPage as $i => $k ) {

                        $data['nav_name'] = $k['webpage_name'];
                        $data['uid'] = $_SESSION['_User']['uid'];
                        $data['is_show'] = 0;
                        $data['linkurl'] = C('WEB_URL_BLOG').'/Webpage/page/webpage_id/'.$i;
                        $data['nav_type'] = 'webpage';
                        $data['nav_position'] = $_POST['nav_position'];

                        if ($BlogNav->add($data)) {
                            $result = true;
                        }
                    }

                    if( $result ) {
                        $this->success('数据添加成功！', appUrl( $sUrl ));
                    } else {
                        $this->error('数据添加失败！');
                    }

                } else {

                    // 单页获取
                    $BlogWebpage = D('BlogWebpage');

                    $map['uid'] = $_SESSION['_User']['uid'];
                    $webPageList = $BlogWebpage->where($map)->select();
                    $this->assign('webPageList', $webPageList);

                    $this->display('nav_webpage');
                }
                break;

            case "del":

                if( $_GET['nav_id'] ) {

                    $sUrl = $_GET['position'] ?  'Blog/nav/position/'.$_GET['position'] : 'Blog/nav';
                    $map['nav_id'] = $_GET['nav_id'];
                    $map['uid'] = $_SESSION['_User']['uid'];

                    $BlogNav->where($map)->delete();
                    $this->success('数据删除成功！', appUrl( $sUrl ));

                } else {

                    $this->error('非法操作！');
                }
                break;
        }




    }

    private function nav_tool() {
        $tools = array(
            array(
                'tag' => 'custom',
                'name' => '自定义导航',
                'url' =>  $_GET['position'] ?  'Blog/nav/position/'.$_GET['position'].'/view/custom' : 'Blog/nav/view/custom'
            ),
            array(
                'tag' => 'cate',
                'name' => '分类导航',
                'url' => $_GET['position'] ? 'Blog/nav/position/'.$_GET['position'].'/view/cate' : 'Blog/nav/view/cate'
            ),
            array(
                'tag' => 'webpage',
                'name' => '单页导航',
                'url' => $_GET['position'] ? 'Blog/nav/position/'.$_GET['position'].'/view/webpage' : 'Blog/nav/view/webpage'
            )
        );

        $this->assign('tools', $tools);
    }


    // 多说管理
    public function duoshuo() {
        $BlogDuoshuo = D('BlogDuoshuo');

        if ( $_POST['submit'] ) {

            if( $BlogDuoshuo->create() ) {

                if ( $BlogDuoshuo->duoshuo_id ) {

                    if( $BlogDuoshuo->save() ) {
                        $this->success('数据保存成功！', appUrl('Blog/duoshuo'));
                    } else {
                        $this->error('数据保存失败！');
                    }

                } else {

                    $BlogDuoshuo->uid = $_SESSION['_User']['uid'];
                    if( $BlogDuoshuo->add() ) {
                        $this->success('数据添加成功！', appUrl('Blog/duoshuo'));
                    } else {
                        $this->error('数据添加失败！');
                    }
                }

            } else {
                $this->error('数据添加失败！');
            }

        } else {

            // 获取站点基本信息
            $map['uid'] = $_SESSION['_User']['uid'];
            $obj = $BlogDuoshuo->where($map)->find();
            $this->assign('obj', $obj);

            $this->display('duoshuo');
        }
    }



    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 日志管理
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    // 日志分类
    public function noteCate() {

        $view = $_GET['view'] ? $_GET['view'] : 'list';
        $ArticleCategory = D('ArticleCategory');

        switch ( $view ) {
            case "list":

                if( $_POST['submit'] ) {

                    $sUrl = 'Blog/noteCate';

                    $cate = $_POST['cate'];
                    $result = false;
                    foreach( $cate as $i => $k ) {

                        $map['catid'] = $i;
                        $data['listorder'] = $k['listorder'];

                        if( $ArticleCategory->where($map)->save($data) ) {
                            $result = true;
                        }
                    }

                    if( $result ) {
                        $this->success('数据保存成功！', appUrl($sUrl));
                    } else {
                        $this->error('数据保存失败！');
                    }

                } else {

                    $map['module_tag'] = "note";
                    $map['uid'] = $_SESSION['_User']['uid'];
                    $list = $ArticleCategory->getListWithCount( $map );
                    $this->assign('list', $list);
                    $this->display('note_cate_list');

                }

                break;

            case "add":

                if ( $_POST['submit'] ) {

                    // 添加日志分类
                    if($ArticleCategory->create()){

                        if( $ArticleCategory->catid) {

                            if( $ArticleCategory->save() ) {
                                $this->success('数据保存成功！', appUrl('Blog/noteCate/view/add/catid/'.$_POST['catid']));
                            } else {
                                $this->error('数据保存失败！');
                            }
                        }else{
                            $ArticleCategory->module_tag = "note";
                            $ArticleCategory->is_sys = 0;
                            $ArticleCategory->uid = $_SESSION['_User']['uid'];
                            if( $ArticleCategory->add() ) {
                                $this->success('数据添加成功！', appUrl('Blog/noteCate'));
                            } else {
                                $this->error('数据添加失败！');
                            }

                        }
                    } else {
                        $this->error('数据添加失败！');
                    }

                } else {

                    $catid = $_GET['catid'];
                    $map['catid'] = $catid;
                    $obj = $ArticleCategory->where($map)->find();

                    $this->assign('obj', $obj);
                    $this->display('note_cate_add');
                }


                break;

            case 'del':

                if( $_GET['catid'] ) {

                    $map['catid'] = $_GET['catid'];
                    $map['uid'] = $_SESSION['_User']['uid'];

                    // 检测该分类下是否存在数据
                    if( !D('Article')->where($map)->find() ) {
                        $ArticleCategory->where($map)->delete();
                        $this->success('数据删除成功！', appUrl('Blog/noteCate'));
                    } else {
                        $this->error('改分类下存在数据，请先删除分类下的数据！');
                    }

                } else {

                    $this->error('非法操作！');
                }
                break;
        }

    }


    // 日志管理
    public function note() {

        $view = $_GET['view'] ? $_GET['view'] : 'list';
        $Article = D('Article');
        $ArticleCategory = D('ArticleCategory');

        switch( $view ) {
            case "list":

                if( $_POST['submit'] ) {
                    $tool = $_POST['tool'];
                    $articleids = $_POST['article_id'];

                    if ( !count($articleids) ) {
                        $this->error('请选择要操作的选项！');
                        exit();
                    }

                    $sUrl = 'Blog/note';
                    switch( $tool ) {
                        case "delAll":


                            foreach( $articleids as $k=>$v ) {
                                $map['article_id'] = $k;
                                $map['uid'] = $_SESSION['_User']['uid'];

                                $Article->where($map)->delete();
                            }

                            $this->success('数据删除成功！', appUrl($sUrl));
                            break;
                    }


                } else {

                    $ArticleCategory = D('ArticleCategory');

                    $map['is_sys'] = 1;
                    $map['_query'] = 'uid='.$_SESSION['_User']['uid'].'&is_sys=0';
                    $map['_logic'] = 'or';
                    $cateList = $ArticleCategory->where($map)->order('listorder')->select();
                    $this->assign('cateList', $cateList);


                    $page = $_GET['page'] ? $_GET['page'] : 1;
                    $pagesize = 10;
                    $pageset = ( $page -1) * $pagesize;
                    $limit = array('page'=> $pageset, 'pagesize'=> $pagesize);

                    $map = array();

                    if( $_GET['submit'] == 'search' ) {
                        $map['catid'] = $_GET['catid'];
                        $this->assign('searchCatid', $_GET['catid']);
                    }

                    $map['status'] = 1;
                    $map['module_tag'] = 'note';
                    $map['uid'] = $_SESSION['_User']['uid'];
                    $list = $Article->getListWithJoin($map, $limit);
                    $this->assign('list', $list);

                    $pageHtml = page( $Article->getCount($map), $page, $pagesize);
                    $this->assign('pageHtml', $pageHtml);

                    $this->display('note_list');
                }


                break;

            case "add":

                if ( $_POST['submit'] ) {

                    if( $Article->create() ) {

                        $Article->data_format();

                        $sUrl = 'Blog/note/view/add/article_id/'.$_POST['article_id'];

                        if( $Article->article_id) {

                            if( $Article->save() ) {
                                $this->success('数据保存成功！', appUrl($sUrl));
                            } else {
                                $this->error('数据保存失败！');
                            }
                        }else{

                            $Article->module_tag = "note";
                            if( $Article->add() ) {
                                $this->success('数据添加成功！', appUrl($sUrl));
                            } else {
                                $this->error('数据添加失败！');
                            }

                        }

                    } else {

                        $this->error('数据添加失败！');
                    }

                } else {

                    // 编辑状态
                    $map = array();
                    $map['article_id'] = $_GET['article_id'];
                    $obj = $Article->where($map)->find();
                    $this->assign('obj', $obj);


                    // 文章分类获取
                    $map['is_sys'] = 1;
                    $map['_query'] = 'uid='.$_SESSION['_User']['uid'].'&is_sys=0';
                    $map['_logic'] = 'or';
                    $cateList = $ArticleCategory->where($map)->order('listorder')->select();
                    $this->assign('cateList', $cateList);

                    $this->display('note_add');
                }

                break;

            case "del":
                if( $_GET['article_id'] ) {

                    $map['article_id'] = $_GET['article_id'];
                    $map['uid'] = $_SESSION['_User']['uid'];

                    $Article->where($map)->delete();
                    $this->success('数据删除成功！', appUrl('Blog/note'));

                } else {

                    $this->error('非法操作！');
                }
                break;
        }

    }




    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 单页管理
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */

    // 单页管理
    public function webpage() {
        $view = $_GET['view'] ? $_GET['view'] : 'list';
        $BlogWebpage = D('BlogWebpage');

        switch ( $view ) {
            case "list":

                if ( $_POST['submit'] ) {

                    $tool = $_POST['tool'];
                    $webpageids = $_POST['webpage_id'];

                    if ( !count($webpageids) ) {
                        $this->error('请选择要操作的选项！');
                        exit();
                    }

                    $sUrl = 'Blog/webpage';
                    switch( $tool ) {
                        case "delAll":


                            foreach( $webpageids as $k=>$v ) {
                                $map['webpage_id'] = $k;
                                $map['uid'] = $_SESSION['_User']['uid'];

                                $BlogWebpage->where($map)->delete();
                            }

                            $this->success('数据删除成功！', appUrl($sUrl));
                            break;
                    }

                } else {

                    $page = $_GET['page'] ? $_GET['page'] : 1;
                    $pagesize = 10;
                    $pageset = ( $page -1) * $pagesize;
                    $limit = array('page'=> $pageset, 'pagesize'=> $pagesize);

                    $map['uid'] = $_SESSION['_User']['uid'];
                    $list = $BlogWebpage->getList($map, $limit);
                    $this->assign('list', $list);

                    $pageHtml = page( $BlogWebpage->getCount($map), $page, $pagesize);
                    $this->assign('pageHtml', $pageHtml);


                    $this->display('webpage');
                }
                break;

            case "add":

                if ( $_POST['submit'] ) {

                    if( $BlogWebpage->create() ) {

                        $BlogWebpage->data_format();

                        $sUrl = 'Blog/webpage/view/add/webpage_id/'.$_POST['webpage_id'];

                        if( $BlogWebpage->webpage_id) {

                            if( $BlogWebpage->save() ) {
                                $this->success('数据保存成功！', appUrl($sUrl));
                            } else {
                                $this->error('数据保存失败！');
                            }
                        }else{

                            $BlogWebpage->uid = $_SESSION['_User']['uid'];
                            if( $BlogWebpage->add() ) {
                                $this->success('数据添加成功！', appUrl($sUrl));
                            } else {
                                $this->error('数据添加失败！');
                            }

                        }

                    } else {

                        $this->error('数据添加失败！');
                    }

                } else {

                    $map['webpage_id'] = $_GET['webpage_id'];
                    $obj = $BlogWebpage->where($map)->find();
                    $this->assign('obj', $obj);

                    $this->display('webpage_add');
                }
                break;

            case "del":

                if( $_GET['webpage_id'] ) {

                    $map['webpage_id'] = $_GET['webpage_id'];
                    $map['uid'] = $_SESSION['_User']['uid'];

                    $BlogWebpage->where($map)->delete();
                    $this->success('数据删除成功！', appUrl('Blog/webpage'));

                } else {

                    $this->error('非法操作！');
                }
                break;
        }

    }



    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     *
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */


}


?>