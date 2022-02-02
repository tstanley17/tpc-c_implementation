<?php


class payment {

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
	public function customer_by_id($w_id, $d_id, $c_id) {
		// Get customer based on last name
		$get_customers = 'SELECT C_ID, C_W_ID, C_D_ID, C_FIRST, C_MIDDLE, C_LAST, C_STREET_1, C_STREET_2, C_CITY, C_STATE, C_ZIP, C_PHONE, C_SINCE, C_CREDIT, C_CREDIT_LIM, C_DISCOUNT, C_BALANCE, C_YTD_PAYMENT, C_PAYMENT_CNT, C_DATA'.
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
	
	
	/**
	* Get customer information using NURand()
	* Get warehouse information and update
	* Get district information and update
	* Check Credit
	* Insert into history table
	*/
	public function payment_transaction($w_id) {
		try {
			// Begin transaction
			$this->pdo->beginTransaction();
					
			$x1_100 = rand(1,100);
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
			$customer_array = $this->customer_by_id($w_id, $d_id, $c_id);
			if(sizeof($customer_array) == 0) { $this->pdo->rollBack(); return false; }

			
			if($x1_100 <= 85) {
				// Home warehouse
				// C_D_ID = D_ID && C_W_ID = W_ID
				$wtemp = array("old_c_w_id" => $customer_array["C_W_ID"]);
				$dtemp = array("old_c_d_id" => $customer_array["C_D_ID"]);
				$customer_array = $customer_array + $wtemp + $dtemp;
			} else {
				// Remote warehouse
				$wtemp = array("old_c_w_id" => $customer_array["C_W_ID"]);
				$dtemp = array("old_c_d_id" => $customer_array["C_D_ID"]);
				$customer_array = $customer_array + $wtemp + $dtemp;
				$customer_array["C_W_ID"] = rand(1,10);
				$customer_array["C_D_ID"] = rand(1,10);
			}
			
			
			$h_amount = rand(1,5000);
			$current_date = date("Y-m-d h:i:s");
			
			// Do updates for customer payments in info array
			$customer_array["C_BALANCE"] -= $h_amount;
			$customer_array["C_YTD_PAYMENT"] += $h_amount;
			$customer_array["C_PAYMENT_CNT"]++;
			
			$warehouse = 'SELECT W_ID, W_NAME, W_STREET_1, W_STREET_2, W_CITY, W_STATE, W_ZIP, W_YTD'.
									' FROM warehouse WHERE W_ID =:w_id; ';
			$stmt = $this->pdo->prepare($warehouse);
			$stmt->execute(array(":w_id" => $w_id));
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$warehouse_info = $result[0];
			if(sizeof($warehouse_info) == 0) { $this->pdo->rollBack(); return false; }

			$stmt->closeCursor();
			
			$warehouse_info["W_YTD"] += $h_amount; // Increment w_ytd by h_amount
			// Update W_YTD in DB
			$update_warehouse = 'UPDATE warehouse '.
								'SET W_YTD = :ytd_w WHERE W_ID =:w_id; ';
			$stmt = $this->pdo->prepare($update_warehouse);
			$stmt->execute(array(":ytd_w" => $warehouse_info["W_YTD"], ":w_id" => $w_id));	
			
			// Get district information
			$get_district = 'SELECT D_ID, D_NAME, D_STREET_1, D_STREET_2, D_CITY, D_STATE, D_ZIP, D_YTD'.
							' FROM district WHERE D_ID =:d_id and D_W_ID =:w_id; ';
			$stmt = $this->pdo->prepare($get_district);
			$stmt->execute(array(":d_id"=>$d_id,":w_id"=>$w_id));
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$district_info = $result[0];
			if(sizeof($district_info) == 0) { $this->pdo->rollBack(); return false; }

			// Update D_YTD
			$district_info["D_YTD"] += $h_amount; // Update d_ytd with h_amount
			$update_district = 'UPDATE district '.
								'SET D_YTD = :d_ytd WHERE D_ID =:d_id; ';
			$stmt = $this->pdo->prepare($update_district);
			$stmt->execute(array(":d_ytd" => $district_info["D_YTD"], ":d_id" => $d_id));
			
			// Update customer balance, payment count, and ytd
			$update_customer = 'UPDATE customer'.
								' SET C_BALANCE =:c_bal ,'.
								' C_YTD_PAYMENT=:c_ytd ,'.
								' C_PAYMENT_CNT =:c_payment_cnt'.
								' WHERE C_ID =:c_id; ';
			$stmt = $this->pdo->prepare($update_customer);
			$stmt->execute(array(":c_bal"=>$customer_array["C_BALANCE"],":c_ytd"=>$customer_array["C_YTD_PAYMENT"],":c_payment_cnt"=>$customer_array["C_PAYMENT_CNT"],":c_id"=>$customer_array["C_ID"]));
			
			// If credit = BC, then update customer data
			if($customer_array["C_CREDIT"] == "BC") { 
				$old = $customer_array["C_DATA"];
				$new = $customer_array["C_ID"] . " " . $customer_array["old_c_d_id"] . " " .
						$customer_array["old_c_w_id"] . " " . $customer_array["C_D_ID"] . " " .
						$customer_array["C_W_ID"] . " " . $h_amount . "        ";
				$new_c_data = $new . $old;
				$update_c_data = "UPDATE customer".
								" SET C_DATA=:c_data".
								" WHERE C_ID=:c_id".
								" AND C_W_ID=:w_id".
								" AND C_D_ID=:d_id; ";
				$stmt = $this->pdo->prepare($update_c_data);
				$stmt->execute(array(":c_data"=>$new_c_data, ":c_id"=>$customer_array["C_ID"], ":w_id"=>$customer_array["old_c_w_id"], ":d_id"=>$customer_array["old_c_d_id"]));
			}
			
			// Insert new row into history table
			$insert_history = 'INSERT INTO history(H_C_ID, H_C_D_ID, H_C_W_ID, H_D_ID, H_W_ID, H_DATE, H_AMOUNT, H_DATA)'.
								' VALUES (:c_id, :c_d_id, :c_w_id, :d_id, :w_id, :date, :amount, :data); ';
			$stmt = $this->pdo->prepare($insert_history);
			$stmt->execute(array(":c_id"=>$customer_array["C_ID"],
								":c_d_id"=>$customer_array["C_D_ID"],
								":c_w_id"=>$customer_array["C_W_ID"],
								":d_id"=>$d_id,
								":w_id"=>$w_id,
								":date"=>$current_date,
								":amount"=>$h_amount,
								":data"=>($warehouse_info["W_NAME"]."    ".$district_info["D_NAME"])
								));
			
			$out = array(
				"date"=>$current_date,
				"d"=>$district_info,
				"w"=>$warehouse_info,
				"c"=>$customer_array,
				"a"=>$h_amount
			);
			
			// commit the transaction
			$this->pdo->commit();
			return $out;
			
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			die($e->getMessage());
		}
	}
	
	public function display_payment($in) {
		
		echo "<style>";
		include 'webStyles.css';
		echo "</style>";
		echo "<table class='formInput'>".
				"<tr><th>Date: ".$in['date']."</th></tr>".
				"<tr><th>Warehouse: ".$in['w']['W_ID']."</th></tr>".
				"<tr>";
				foreach($in['w'] as $key=>$i) {
					echo "<td>". $key . " = " . $i . "</td>";
				}
			echo "</tr>";
			echo "<tr><th>District: ".$in['d']['D_ID']."</th></tr>".
				"<tr>";
				foreach($in['d'] as $key=>$i) {
					echo "<td>". $key . " = " . $i . "</td>";
				}
			echo "</tr>";
			echo "<tr><th>Customer: ".$in['c']['C_ID']."</th></tr>".
				"<tr>";
				foreach($in['c'] as $key=>$i) {
					if($key == "C_ID" || $key == "C_DATA") { continue; }
					if( $key == "C_PHONE" ) { echo "</tr><tr>"; }
					echo "<td>". $key . " = " . $i . "</td>";
				}
			echo "</tr>";
			echo "<tr><th>Amount paid: </th><th></th><th> ".$in['a']."</th></tr>".
				"<tr><th>Credit Limit: </th><th></th><th> ".$in['c']['C_CREDIT_LIM']."</th></tr>".
				"<tr><th>C_DATA: ".$in['c']['C_DATA']."</th></tr>".	
			"</table>";
		
		echo "<br><br><br>";
		echo "<form action='newOrderInput.php'><input style='width: 150px;' type='submit' value='Back'></form>";
	}
	
}


// ------------------------------------ test the payment transaction ------------------------------------

	$paymentobj = new payment();
	$paymentobj->construct();
	$committed = $paymentobj->payment_transaction(1);
	$paymentobj->destruct();
	if(!empty($committed)){
		$paymentobj->display_payment($committed);
	} else {
		echo "Payment was unsuccessful.";
	}
	
// ------------------------------------ test the payment transaction ------------------------------------



