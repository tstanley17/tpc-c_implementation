<?php

class delivery {

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
	public function delivery_customer_by_id($w_id, $d_id, $c_id) {
		// Get customer based on last name
		$get_customers = 'SELECT C_ID, C_DELIVERY_CNT, C_BALANCE'.
						' FROM customer WHERE C_ID =:c_id'.
						' AND C_W_ID=:w_id'.
						' AND C_D_ID=:d_id; ';
		$stmt = $this->pdo->prepare($get_customers);
		$stmt->execute(array(":c_id" => $c_id, ":w_id" => $w_id, ":d_id" => $d_id));
			if($stmt == false) { echo "No customer found by id"; $this->pdo->rollBack(); }
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		$customer_array = $result[0];
		
		return $customer_array;
	}
	
	public function delivery_transaction($w_id) {
		try {
			// Set values
			$w_id = 0;
			$d_id = rand(0,9);
			$c_id = rand(0, 2999);
			
			// Start time
			$start_time = microtime(true);
			
			// Begin transaction
			$this->pdo->beginTransaction();
					
			// Generate district ID, always int from 1 to 10
			$carrier_id = rand(0,9);
			// Generate delivery date
			$delivery_date = 'CURRENT_TIMESTAMP()';

			
			// Get input from deferred execution queue
			$get_neworder = "SELECT NO_O_ID".
							" FROM new_order".
							" WHERE NO_W_ID =:w_id".
							" AND NO_D_ID =:d_id".
							" ORDER BY NO_O_ID ASC".
							" LIMIT 1; ";
			$stmt = $this->pdo->prepare($get_neworder);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id));
			if($stmt == false) { echo "No new order found."; $this->pdo->rollBack(); }
			$no_o_id = $stmt->fetchColumn();			
			
			// Delete selected row from the new_order table
			$delete_new_order = "DELETE FROM order_line".
									" WHERE NO_W_ID =:w_id".
									" AND NO_D_ID =:d_id".
									" AND NO_O_ID =:o_id; ";
			$stmt = $this->pdo->prepare($delete_new_order);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id, ":o_id" => $no_o_id));
			if($stmt == false) { echo "Deletion from new order failed."; $this->pdo->rollBack(); }
			
			
			// Get order information
			$get_order_info = "SELECT O_C_ID, O_CARRIER_ID".
								" FROM orders WHERE O_W_ID = :w_id".
								" AND O_D_ID = :d_id".
								" AND O_ID = :o_id; ";
			$stmt = $this->pdo->prepare($get_order_info);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id, ":o_id" => $no_o_id));
			if($stmt == false) { echo "No order information found."; $this->pdo->rollBack(); }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$order_info = $result[0];
			$o_c_id = $order_info['O_C_ID'];
			
			// Get order line info
			$get_order_line_info = "SELECT OL_NUMBER, OL_AMOUNT FROM order_line".
									" WHERE OL_W_ID =:w_id".
									" AND OL_D_ID =:d_id".
									" AND OL_O_ID =:o_id; ";
			$stmt = $this->pdo->prepare($get_order_line_info);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id, ":o_id" => $no_o_id));
			if($stmt == false) { echo "No order line information found."; $this->pdo->rollBack(); }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$order_line_info = $result;
			
			$update_order_line = "UPDATE order_line".
									" SET OL_DELIVERY_D =:delivery_d".
									" WHERE OL_W_ID =:w_id".
									" AND OL_D_ID =:d_id".
									" AND OL_O_ID =:o_id".
									" AND OL_NUMBER =:ol_num; ";
			// Update order line date and get sum
			$ol_sum = 0;
			foreach($order_line_info as $row) {
				$stmt = $this->pdo->prepare($update_order_line);
				$stmt->execute(array(":delivery_d"=>$delivery_date,":w_id" => $w_id, ":d_id" => $d_id, ":o_id" => $no_o_id,"ol_num"=>$row["OL_NUMBER"]));
				if($stmt == false) { echo "order line update failed."; $this->pdo->rollBack(); }
				$ol_sum += $row["OL_AMOUNT"];
			}
			
			// Get customer information
			$customer_array = $this->delivery_customer_by_id($w_id, $d_id, $c_id);
			
			// Update customer balance and delivery count
			$update_customer = "UPDATE customer".
								" SET C_BALANCE =:new_bal,".
								" SET C_DELIVERY_CNT =:new_cnt".
								" WHERE C_ID =:c_id".
								" AND C_W_ID=:w_id".
								" AND C_D_ID=:d_id; ";
			$stmt = $this->pdo->prepare($update_customer);
			$stmt->execute(array(":new_bal"=>($customer_array["C_BALANCE"] + $ol_sum),":new_cnt"=>($customer_array["C_DELIVERY_CNT"] +1), ":w_id" => $w_id, ":d_id" => $d_id, ":c_id"=>$o_c_id));
			if($stmt == false) { echo "Customer update failed."; $this->pdo->rollBack(); }
			
			// commit the transaction
			$this->pdo->commit();
			
			// End time 
			$end_time = microtime(true);
			$run_time = $end_time - $start_time;
			$time = array(
				"start"=>$start_time,
				"end"=>$end_time,
				"run"=>$run_time
			);
			
			$out = array(
				"w"=>$w_id,
				"c"=>$customer_array,
				"o"=>$order_info,
				"t"=>$time
			);
			
			return $out;
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			die($e->getMessage());
		}	
	}
	
	public function display_delivery($in) {
		echo "<style>";
		include 'webStyles.css';
		echo "</style>";
		
		echo "<table class='formInput'>".
			"<tr><th>Warehouse: ".$in['w']."</th></tr>".
			"<tr><th>Carrier Number: ".$in['o']['O_CARRIER_ID']."</th></tr>".
			"<tr><th>Execution Status: Delivery has been  queued</th></tr>";
		echo "</table>";
		
		echo "<br><br><br>";
		echo "<form action='newOrderInput.php'><input style='width: 150px;' type='submit' value='Back'></form>";
	}
	public function log_data($in) {
		$file = fopen("resultFile.txt", "a") or die("Unable to open file!");
		$txt = "Start time: " . $in['t']['start'] . " sec\n".
				"Warehouse: " . $in['w'] . "\n".
				"Carrier: " . $in['o']['O_CARRIER_ID'] . "\n".
				"End time: " . $in['t']['end'] . " sec\n".
				"Total time: " . $in['t']['run'] . " sec\n\n";
		fwrite($file, $txt);
		fclose($file);
		echo "File write complete.";
	}

}



// ------------------------------------ test the payment transaction ------------------------------------
	
	$delivery_obj = new delivery();
	$delivery_obj->construct();
	$committed = $delivery_obj->delivery_transaction(1);
	$delivery_obj->destruct();
	if(!empty($committed)){
		$delivery_obj->display_delivery($committed);
		$delivery_obj->log_data($committed);
	} else {
		echo "Order status retrieval was unsuccessful.";
	}
	
	
// ------------------------------------ test the payment transaction ------------------------------------



