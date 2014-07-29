$helloWorld: <<?php echo $this->_var['helloWorld']; ?>> <br/>
A
SDF
AS
DF
ADSFAS
DF
SAFdddd <br/>

$helloWorld1: <?php echo $this->_var['helloWorld1']; ?> <br/>
$all:
<?php $_from = $this->_var['all']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); };if (count($_from)):
    foreach ($_from AS $this->_var['itemList']):
?>
    <?php $_from = $this->_var['itemList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
        <?php echo $this->_var['key']; ?> : <?php echo $this->_var['item']; ?>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?><br/>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<br/>