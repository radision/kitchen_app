<?php

$merchant_id = isset($_GET['m']) ? intval($_GET['m']) : 1;
$mobile = isset($_GET['mobile']) ? htmlspecialchars(trim($_GET['mobile'])) : '';
$orders = isset($_GET['order']) ? trim($_GET['order']) : '';

$curr_time = time();
$amount = 0;
$default_address = array(
    'city' => '北京',
    'district' => '朝阳',
    'garden' => '康营家园',
    'room' => '',
);

include('config/database.php');
$link = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($link->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$merchant = $consumer_mobile = $address = array();

if ($merchant_id)
{
    $sql = "SELECT * FROM merchant WHERE merchant_id='{$merchant_id}'";
    if ($result = $link->query($sql))
    {
        $merchant = $result->fetch_array();
        $result->free();
    }
}

$consumer_mobile_id = 0;
if ($mobile)
{
    $sql = "SELECT * FROM consumer_mobile WHERE mobile_no='{$mobile}'";
    if ($result = $link->query($sql))
    {
        $consumer_mobile = $result->fetch_array();
        $result->free();
    }
    if (!$consumer_mobile)
    {
        $sql = "INSERT INTO consumer_mobile (mobile_no, status, created_at, updated_at) VALUES ('{$mobile}', '1', '{$curr_time}', '{$curr_time}')";
        $link->query($sql);
        $consumer_mobile_id = $link->insert_id;
    }
    else
    {
        $consumer_mobile_id = $consumer_mobile['consumer_mobile_id'];
    }
}

if ($consumer_mobile_id)
{
    $sql = "SELECT * FROM address WHERE consumer_mobile_id='{$consumer_mobile_id}' AND status='1'";
    if ($result = $link->query($sql))
    {
        while($row = $result->fetch_array())
        {
            $address[] = $row;
        }
        $result->free();
    }
}

// 计算总金额 生成订单
if ($orders)
{
    $orders_arr = explode('|', $orders);
    foreach ($orders_arr as $row)
    {
        if (!$row)
        {
            continue;
        }
        list($goods_id, $count) = explode(':', $row);
        $sql = "SELECT price FROM goods WHERE goods_id = '{$goods_id}'";
        if ($result = $link->query($sql))
        {
            $goods = $result->fetch_array();
            $price = $goods['price'];
            $result->free();
        }
        $amount += $count * $price;
    }
}

echo file_get_contents('header.html');
?>
<input type="hidden" id="m" value="<?php echo $merchant_id; ?>">
<input type="hidden" id="mb" value="<?php echo $consumer_mobile_id; ?>">
<input type="hidden" id="order" value="<?php echo $orders; ?>">
<div class="col-md-12 col-sm-12">
    <div class="row">
        <div class="my-panel">
            <h1 class="text-center"><span class="label label-default"><?php echo $merchant['title']; ?></span></h1>
        </div>
    </div>
</div>
<div class="col-md-12 col-sm-12">
    <div class="row">
        <div class="my-panel">
            <div class="form-group input-group">
                <span class="input-group-addon">总金额</span>
                <input type="text" class="form-control" value="<?php echo $amount; ?>" readonly>
            </div>
        </div>
    </div>
</div>
<?php
if ($address)
{
    foreach ($address as $row)
    {
?>
<div class="col-md-12 col-sm-12">
    <div class="row">
        <div class="my-panel">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <label><input type="radio" id="address_<?php echo $row['address_id']; ?>" name="address_id" value="<?php echo $row['address_id']; ?>"> 送到此地址</label>
                </div>
                <div class="panel-body">
                <input class="col-md-4 col-sm-12" value="<?php echo $row['city']; ?>" readonly>
                <input class="col-md-4 col-sm-12" value="<?php echo $row['district']; ?>" readonly>
                <input class="col-md-4 col-sm-12" value="<?php echo $row['garden']; ?>" readonly>
                <input class="col-md-12 col-sm-12" value="<?php echo $row['room']; ?>" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }
}
?>
<div class="col-md-12 col-sm-12">
    <div class="row">
        <div class="my-panel">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <label><input type="radio" id="address_0" name="address_id" value="0"> 送到新地址</label>
                </div>
                <div class="panel-body form-group">
                    <input id="new_city" class="col-md-4 col-sm-12" value="<?php echo $default_address['city']; ?>" readonly>
                    <input id="new_district" class="col-md-4 col-sm-12" value="<?php echo $default_address['district']; ?>" readonly>
                    <input id="new_garden" class="col-md-4 col-sm-12" value="<?php echo $default_address['garden']; ?>">
                    <input id="new_room" class="col-md-12 col-sm-12" value="" placeholder="楼号、门牌号">
                </div>
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
            <button id="btn_next" type="button" class="btn btn-success btn-lg pull-right">去付款</button>
        </div>
    </div>
</div>

<script>

$('#btn_next').click(function(){
    init_error();
    var address_id = 0;
    $('input[id^="address_"]:checked').each(function(idx, obj){
        address_id = $(obj).val();
    });
    var new_city = $('#new_city').val();
    var new_district = $('#new_district').val();
    var new_garden = $('#new_garden').val();
    var new_room = $('#new_room').val();
    if (address_id == 0) {
        clean_error_class($('#new_garden'));
        if (new_garden == '') {
            add_error_class($('#new_garden'));
            show_error('请填写小区名称');
            return false;
        }
        clean_error_class($('#new_room'));
        if (new_room == '') {
            add_error_class($('#new_room'));
            show_error('请填写楼号、房间号');
            return false;
        }
    }
    var merchant_id = parseInt($('#m').val());
    var consumer_mobile_id = parseInt($('#mb').val());
    var orders = $('#order').val();
    if (address_id == 0) {
        window.location.href = '/pay_wechat.php?m=' + merchant_id + '&mb=' + consumer_mobile_id + '&order=' + orders + '&address=' + address_id + '&city=' + new_city + '&district=' + new_district + '&garden=' + new_garden + '&room=' + new_room;
        return false;
    }
    window.location.href = '/pay_wechat.php?m=' + merchant_id + '&mb=' + consumer_mobile_id + '&order=' + orders + '&address=' + address_id;
});
</script>

</body>
</html>
