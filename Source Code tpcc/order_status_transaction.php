<?php


class order_status {

    const DB_HOST = 'localhost';
    const DB_NAME = 'tpccmodel';
    const DB_USER = 'root';
    const DB_PASSWORD = 'Basketball14!';

    /**
     * Open the database connection
     */
    public function construct() {
        // open database connection
        $conStr = sprintf("mysql:host=%s;dbname=%s", self::DB_HOST, self::DB_NAME);
        try {
            $this->pdo = new PDO($conStr, self::DB_USER, self::DB_PASSWORD);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
	
	 /**
     * close the database connection
     */
    public function destruct() {
        // close the database connection
        $this->pdo = null;
    }
	
    /**
     * PDO instance
     * @var PDO 
     */
    private $pdo = null;
	
	/**
	* return customer information based on customer ID
	*/
	public function order_status_customer_by_id($w_id, $d_id, $c_id) {
		// Get customer based on last name
		$get_customers = 'SELECT C_ID, C_FIRST, C_MIDDLE, C_LAST, C_BALANCE'.
						' FROM customer WHERE C_ID =:c_id'.
						' AND C_W_ID=:w_id'.
						' AND C_D_ID=:d_id; ';
		$stmt = $this->pdo->prepare($get_customers);
		$stmt->execute(array(":c_id" => $c_id, ":w_id" => $w_id, ":d_id" => $d_id));
		if($stmt == false) { echo "No customer found by id"; }
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		$customer_array = $result[0];
		
		return $customer_array;
	}
	
	
	public function order_status_transaction($w_id) {
		try {
			// Begin transaction
			$this->pdo->beginTransaction();
			
			$y1_100 = rand(1,100);
			
			// Set values
			$w_id = 0;
			$d_id = rand(0,9);
			$c_id = rand(0, 2999);
			
			// Below is the correct tpc-c transaction guidelines, but due to our
			// database implementation, we cannont use the NURand function to retreive a customer
/*
			// Get customers based on last or id
			if($y1_100 <=60) {
				// Get customer by last name
				$c_last = $this->NURand(255,0,999);  // Double check NURand
				$customer_array = $this->order_status_customer_by_last($w_id, $d_id, $c_last);
				if(sizeof($customer_array) == 0) { $this->pdo->rollBack(); return false; }
			} else {
				// Get customer by id
				$c_id = $this->NURand(1023,1,3000);
				$customer_array = $this->order_status_customer_by_id($w_id, $d_id, $c_id);
				if(sizeof($customer_array) == 0) { $this->pdo->rollBack(); return false; }
			}
*/			
			$customer_array = $this->order_status_customer_by_id($w_id, $d_id, $c_id);
			if(sizeof($customer_array) == 0) { $this->pdo->rollBack(); return false; }

			// Get order information
			$get_order_info = "SELECT O_ID, O_ENTRY_D, O_CARRIER_ID".
								" FROM orders WHERE O_W_ID = :c_w_id".
								" AND O_D_ID = :c_d_id".
								" AND O_C_ID = :c_id; ";
			$stmt = $this->pdo->prepare($get_order_info);
			$stmt->execute(array(":c_w_id" => $w_id, ":c_d_id" => $d_id, ":c_id" => $customer_array["C_ID"]));
			if($stmt == false) { echo "No customer found by id"; }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$order_info = $result[0];
			if(sizeof($order_info) == 0) { $this->pdo->rollBack(); return false; }
			
			echo $w_id . " " . $d_id . " " . $order_info['O_ID'];
			// Get order line information
			$get_orderline_info = "SELECT OL_I_ID, OL_SUPPLY_W_ID, OL_QUANTITY, OL_AMOUNT, OL_DELIVERY_D".
								" FROM order_line WHERE OL_W_ID =:w_id".
								" AND OL_D_ID =:d_id".
								" AND OL_O_ID =:o_id; ";
			$stmt = $this->pdo->prepare($get_orderline_info);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id, ":o_id" => $order_info["O_ID"]));
			if($stmt == false) { echo "No info found."; }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$orderline_info = $result;
			print_r($result);
			if(sizeof($orderline_info) == 0) { $this->pdo->rollBack(); return false; }
			print_r($orderline_info);
			$out = array(
				"w"=>$w_id,
				"d"=>$d_id,
				"c"=>$customer_array,
				"o"=>$order_info,
				"ol"=>$orderline_info
			);
			
			// commit the transaction
			$this->pdo->commit();
			return $out;
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			die($e->getMessage());
		}	
	}
	
	public function display_order_status($in) {
		
		echo "<style>";
		include 'webStyles.css';
		echo "</style>";
		
		echo "<table class='formInput'>".
			"<tr><th>Warehouse: ".$in['w']."</th><th>District: ".$in['d']."</th></tr>".
			"<tr><th>Customer: ".$in['c']['C_ID']."</th><th>Customer Name: ".$in['c']['C_LAST']."</th></tr>".
			"<tr><th>Cust-balance: ".$in['c']['C_BALANCE']."</th></tr>".
			"<tr><th>Order-Number: ".$in['o']['O_ID']."</th><th></th><th>Entry-Date: ".$in['o']['O_ENTRY_D']."</tr>".
			"<tr><th>Supply-W</th><th>Item-Id</th><th>Qty</th><th>Amount</th></tr>";
			foreach($in['ol'] as $row) {
				echo "<tr><td>".$row['OL_SUPPLY_W_ID']."</td><td>".$row['OL_I_ID']."</td><td>".$row['OL_QUANTITY']."</td><td>".$row['OL_AMOUNT']."</td></tr>";
			}	
		echo "</table>";
		
		echo "<br><br><br>";
		echo "<form action='newOrderInput.php'><input style='width: 150px;' type='submit' value='Back'></form>";
		
	}

}



// ------------------------------------ test the payment transaction ------------------------------------

	$order_status_obj = new order_status();
	$order_status_obj->construct();
	$committed = $order_status_obj->order_status_transaction(1);
	$order_status_obj->destruct();
	if(!empty($committed)){
		$order_status_obj->display_order_status($committed);
	} else {
		echo "Order status retrieval was unsuccessful.";
	}
	
	
// ------------------------------------ test the payment transaction ------------------------------------



