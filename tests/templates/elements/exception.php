<?php
$this->assign('existing', 'Changed');
$this->assign('added', 'Discarded');
$this->start('incomplete');
echo 'Discarded';
$this->start('nested');

throw new RuntimeException('Element exception.');
