<?php
class Order {
    public $id;
    public $total;
    public $items = [];

    public function __construct($id, $total) {
        $this->id = $id;
        $this->total = $total;
    }
}
?>
