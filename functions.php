<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function themeConfig($form)
{
    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', null, null, _t('站点LOGO地址'), _t('在这里填入一个图片URL地址, 以在网站标题前加上一个LOGO'));
    $form->addInput($logoUrl);
    
    $sidebarBlock = new Typecho_Widget_Helper_Form_Element_Checkbox(
        'sidebarBlock',
    array('ShowSearch' => _t('显示搜索框'),
         'ShowCategory' => _t('显示分类'),
        'ShowRecentPosts' => _t('显示最新文章'),
    
   
    'ShowArchive' => _t('显示归档'),
    'ShowTags' => _t('显示标签')),
    array('ShowSearch', 'ShowCategory', 'ShowRecentPosts', 'ShowArchive', 'ShowTags'),
        _t('侧边栏显示')
    );
    
    $form->addInput($sidebarBlock->multiMode());

    $css = new Typecho_Widget_Helper_Form_Element_Radio(
        'css',
    array(
    'red' => _t('红色系'),
    'green' => _t('绿色系'),
    'blue' => _t('蓝色系'),
    'purple' => _t('紫色'),
    'black' => _t('黑色')
    ),
    'green',
    _t('配色选择')
    );
     
    $form->addInput($css->multiMode());
}
/**
*
*手机移动设备识别函数
*
**/
function is_mobile()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_browser = array(
        "mqqbrowser", //手机QQ浏览器
        "opera mobi", //手机opera
        "juc","iuc",//uc浏览器
        "fennec","ios","applewebKit/420","applewebkit/525","applewebkit/532","ipad","iphone","ipaq","ipod",
        "iemobile", "windows ce",//windows phone
        "240x320","480x640","acer","android","anywhereyougo.com","asus","audio","blackberry",
        "blazer","coolpad" ,"dopod", "etouch", "hitachi","htc","huawei", "jbrowser", "lenovo",
        "lg","lg-","lge-","lge", "mobi","moto","nokia","phone","samsung","sony",
        "symbian","tablet","tianyu","wap","xda","xde","zte"
    );
    $is_mobile = false;
    foreach ($mobile_browser as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}
//加载耗时
function timer_start()
{
    global $timestart;
    $mtime = explode(' ', microtime());
    $timestart = $mtime[1] + $mtime[0];
    return true;
}
timer_start();
 
function timer_stop($display = 0, $precision = 3)
{
    global $timestart, $timeend;
    $mtime = explode(' ', microtime());
    $timeend = $mtime[1] + $mtime[0];
    $timetotal = $timeend - $timestart;
    $r = number_format($timetotal, $precision);
    if ($display) {
        echo $r;
    }
    return $r;
}

/*文章阅读次数含cookie*/
function get_post_view($archive)
{
    $cid    = $archive->cid;
    $db     = Typecho_Db::get();
    $prefix = $db->getPrefix();
    if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')))) {
        $db->query('ALTER TABLE `' . $prefix . 'contents` ADD `views` INT DEFAULT 0;');
        echo 0;
        return;
    }
    $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
    if ($archive->is('single')) {
        $views = Typecho_Cookie::get('extend_contents_views');
        if (empty($views)) {
            $views = array();
        } else {
            $views = explode(',', $views);
        }
        if (!in_array($cid, $views)) {
            $db->query($db->update('table.contents')->rows(array('views' => (int) $row['views'] + 1))->where('cid = ?', $cid));
            array_push($views, $cid);
            $views = implode(',', $views);
            Typecho_Cookie::set('extend_contents_views', $views); //记录查看cookie
        }
    }
    echo $row['views'];
}
function threadedComments($comments, $options)
{
    $commentClass = '';
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {
            $commentClass .= ' comment-by-author';
        } else {
            $commentClass .= ' comment-by-user';
        }
    }
    $commentLevelClass = $comments->levels > 0 ? ' comment-child' : ' comment-parent'; ?>

<li id="li-<?php $comments->theId(); ?>" class="comment-body<?php
if ($comments->levels > 0) {
        echo ' comment-child';
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass; ?>">
    <div id="<?php $comments->theId(); ?>">
        <div class="comment-author">
            <?php
            //头像CDN by Rich
            $host = 'https://gravatar.loli.net'; //自定义头像CDN服务器
            $url = '/avatar/'; //自定义头像目录,一般保持默认即可
            $rating = Helper::options()->commentsAvatarRating;
    $hash = md5(strtolower($comments->mail));
    $email = strtolower($comments->mail);
    $qq=str_replace('@qq.com', '', $email);
    if (strstr($email, "qq.com") && is_numeric($qq) && strlen($qq) < 11 && strlen($qq) > 4) {
        $avatar = '//q.qlogo.cn/g?b=qq&nk='.$qq.'&s=100';
    } else {
        $avatar = $host . $url . $hash . '&r=' . $rating . '&d=mm';
    } ?>
            <img class="avatar" src="<?php echo $avatar ?>" alt="<?php echo $comments->author; ?>" width="<?php echo $size ?>" height="<?php echo $size ?>" />
            <cite class="fn"><?php $comments->author(); ?></cite>
        </div>
        <div class="comment-meta">
            <a href="<?php $comments->permalink(); ?>"><?php $comments->date('Y-m-d H:i'); ?></a>
            <span class="comment-reply"><?php $comments->reply(); ?></span>
        </div>
   <?php
$cos = preg_replace('#</?[p|P][^>]*>#', '', $comments->content);
    echo $cos; ?>
    </div>
<?php if ($comments->children) {
        ?>
    <div class="comment-children">
        <?php $comments->threadedComments($options); ?>
    </div>
<?php
    } ?>
</li>
<?php
}
function themeInit($archive)
{
    Helper::options()->commentsMaxNestingLevels = 999;
}
?>
