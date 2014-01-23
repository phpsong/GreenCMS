<?php
/**
 * Created by Green Studio.
 * File: CatsLogic.class.php
 * User: TianShuo
 * Date: 14-1-16
 * Time: 上午12:34
 */

namespace Home\Logic;
use \Think\Model\RelationModel;

/**
 * Class CatsLogic
 * @package Home\Logic
 */
class CatsLogic extends RelationModel {

    /**
     * @param $id 分类id
     * @param bool $relation 是否关联
     * @return mixed 找到之后返回数组
     */
    public function detail( $id, $relation = true ) {
        $map = array();
        $map['cat_id|cat_slug'] = $id;
        return  D( 'Cats' )->where( $map )->relation( $relation )->find();
    }

    /**
     * @param int $limit limit
     * @param bool $relation 是否关联
     * @return mixed
     */
    public function getList( $limit = 20, $relation = true ){
        return  D( 'Cats' )->limit( $limit )->relation( $relation )->find();
    }

    /**
     * @param int $id  分类id
     * @return mixed 找到所有父类
     */
    public function getFather( $id = 0 ) {
            $info = $this->detail( $id );
            if( $info['cat_father'] != 0 ){
                $info['cat_father_detail'] = $this->getFather( $info['cat_father'] );
            }
            return $info;
    }

    /**
     * @param int $id 分类id
     * @return mixed  找到所有子节点
     */
    public function getChildren( $id = 0 ) {
        if( $id ) {
            $info = $this->getChild( $id );
            if( sizeof( $info ) == 1 ){
                $info[0]['cat_children'] = $this->getChild( $info[0]['cat_id'] );
            } else {
                foreach( $info as $key => $value ) {
                    $info[$key]['cat_children'] = $this->getChildren( $value['cat_id'] );
                }
            }
            return $info;
        }
        return false;
    }

    /**
     * @param int $id 分类id
     * @return mixed 返回子节点
     */
    public function getChild( $id = 0 ) {
        if( $id ) {
            $info = D( 'Cats' )->where( array( "cat_father" => $id ) )->select();
            if( $info != null ) return $info;
        }
        return false;
    }

    /**
     * @param $info 分类info
     * @return mixed 找到的话返回post_id数组集合
     */
    public function getPostsId( $info ) {
        $cat_info ['tag_id'] = $info;
        $cat = D ( 'Post_cat' )->field( 'post_id' )->where ( $cat_info )->select();
        foreach ( $cat as $key => $value ) {
            $cat[$key] = $cat[$key]['post_id'];
        }
        return $cat;
    }

    /**
     * @param $cat_id 分类id
     * @param int $num 数量
     * @return mixed
     */
    public function getPostsByCat( $cat_id, $num = 5 ) {
        $cat = $this->getPostIdsByCat( $cat_id );
        $posts = D ( 'Posts','Logic' )->getList( 'single', 'post_id desc', $num, true, 'publish', $cat );
        return $posts;
    }

    /**
     * @param $cat_id 分类id
     * @return mixed
     */
    public function getPostIdsByCat( $cat_id ) {
        $cat = $this->getPosts( $cat_id );
        foreach( $cat as $key => $value ) {
            $cat[$key] = $cat[$key]['post_id'];
        }
        return $cat;
    }
}