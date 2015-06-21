<?php

include('config/database.php');

$merchant_id = isset($_GET['m']) ? intval($_GET['m']) : 1;

$link = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($link->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

include('set_charset.php');

$merchant = $goods = $goods_id = $goods_stock = array();
$today = date('Ymd');
$default_stock = array(
    'total' => 10,
    'ordered' => 0
);

if ($merchant_id)
{
    $sql = "SELECT * FROM merchant WHERE merchant_id='{$merchant_id}'";
    if ($result = $link->query($sql))
    {
        $merchant = $result->fetch_array();
        $result->free();
    }
}

if ($merchant_id)
{
    $sql = "SELECT * FROM goods WHERE merchant_id='{$merchant_id}'";
    if ($result = $link->query($sql))
    {
        while ($row = $result->fetch_array())
        {
            $goods_id[] = $row['goods_id'];
            $goods[$row['goods_id']] = $row;
        }
        $result->free();
    }
}

if ($goods_id)
{
    $goods_id_str = implode(',', $goods_id);
    $sql = "SELECT * FROM goods_stock WHERE goods_id IN ({$goods_id_str}) AND today='{$today}'";
    if ($result = $link->query($sql))
    {
        while ($row = $result->fetch_array())
        {
            $goods_stock[$row['goods_id']] = $row;
            $result->free();
        }
        if (!$goods_stock)
        {
            foreach ($goods_id as $id)
            {
                $sql = "INSERT INTO goods_stock VALUES ('{$id}', '{$default_stock['total']}', '{$default_stock['ordered']}', '{$today}')";
                $link->query($sql);
                $goods_stock[$id] = array(
                    'goods_id' => $id,
                    'total' => $default_stock['total'],
                    'ordered' => $default_stock['ordered'],
                    'today' => $today
                );
            }
        }
    }
}

$link->close();

echo file_get_contents('header.html');
?>

<input type="hidden" id="m" value="<?php echo $merchant_id; ?>">
<div class="row">
    <div class="col-md-12 col-sm-12">
        <h1 class="text-center"><span class="label label-default"><?php echo $merchant['title']; ?></span></h1>
    </div>
<?php
foreach ($goods as $goods_row)
{
?>
    <div class="col-md-12 col-sm-12">
        <h3 class="text-center"><?php echo $goods_row['title']; ?></h3>
    </div>
    <div class="col-md-12 col-sm-12">
        <img class="my-img img-responsive center-block img-thumbnail" src="upload/<?php echo $goods_row['picture_url']; ?>">
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="my-panel panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-shopping-cart fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                    <div class="huge"><?php echo $merchant['shipping']; ?></div>
                        <div>配送范围</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="my-panel panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-shopping-cart fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                    <div class="huge"><?php echo $goods_stock[$goods_row['goods_id']]['total']; ?></div>
                        <div>今日总量(份)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="my-panel panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-shopping-cart fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                    <div class="huge"><?php echo $goods_stock[$goods_row['goods_id']]['total'] - $goods_stock[$goods_row['goods_id']]['ordered']; ?></div>
                        <div>剩余(份)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="row">
            <div class="my-panel">
                <div class="form-group input-group">
                    <span class="input-group-addon">价格</span>
                    <input id="price_<?php echo $goods_row['goods_id']; ?>" data-goods-id="<?php echo $goods_row['goods_id']; ?>" class="form-control" type="text" value="<?php echo $goods_row['price']; ?>" disabled>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="row">
            <div class="my-panel">
                <div class="form-group input-group">
                    <span class="input-group-addon">我要订</span>
                    <input id="amount_<?php echo $goods_row['goods_id']; ?>" data-goods-id="<?php echo $goods_row['goods_id']; ?>" data-total="<?php echo $goods_stock[$goods_row['goods_id']]['total']; ?>" data-ordered="<?php echo $goods_stock[$goods_row['goods_id']]['ordered']; ?>" class="form-control" placeholder="请填写 1 ~ <?php echo $goods_stock[$goods_row['goods_id']]['total'] - $goods_stock[$goods_row['goods_id']]['ordered']; ?> 之间的数字" type="text">
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
    <div class="col-md-12 col-sm-12">
        <div class="row">
            <div class="my-panel">
                <div class="form-group input-group">
                    <span class="input-group-addon">我的电话</span>
                    <input id="mobile" class="form-control" placeholder="请填写您当前正常使用的手机号码" type="text">
                </div>
            </div>
        </div>
    </div>
    <div id="show_error" class="col-md-12 col-sm-12 hide">
        <div class="row">
            <div class="my-panel form-group has-error">
                <label class="control-label"></label>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12">
        <div class="row">
            <div class="my-panel">
                <button id="btn_next" type="button" class="btn btn-success btn-lg pull-right">下一步</button>
            </div>
        </div>
    </div>
</div>
<script>

$('#btn_next').click(function(){
    init_error();
    var order_str = '';
    $('input[id^="amount_"]').each(function(idx, obj){
        var amount = $(obj).val();
        var goods_id = parseInt($(obj).attr('data-goods-id'));
        var total = parseInt($(obj).attr('data-total'));
        var ordered = parseInt($(obj).attr('data-ordered'));
        var allowed = total - ordered;
        clean_error_class($('#amount_' + goods_id).parent());
        if (amount == '') {
            add_error_class($('#amount_' + goods_id).parent());
            show_error('请填写订餐的份数');
            return false;
        }
        amount = parseInt(amount) || 0;
        if (amount == 0) {
            add_error_class($('#amount_' + goods_id).parent());
            show_error('请填写数字');
            return false;
        }
        if (amount > allowed) {
            add_error_class($('#amount_' + goods_id).parent());
            show_error('订餐数量只能是1~' + allowed + '之间的数字');
            return false;
        }
        order_str += goods_id + ':' + amount + '|';
    });

    var mobile = $('#mobile').val();
    clean_error_class($('#mobile').parent());
    if (mobile == '') {
        add_error_class($('#mobile').parent());
        show_error('请填写手机号');
        return false;
    }

    var merchant_id = parseInt($('#m').val());
    window.location.href = '/address.php?m=' + merchant_id + '&mobile=' + mobile + '&order=' + order_str;
});

</script>
</body>
</html>
