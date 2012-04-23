<?php
interface Kuapay_Adapter {
    public function purchase(Kuapay_Purchase $purchase);

    public function status($purchaseId);
}