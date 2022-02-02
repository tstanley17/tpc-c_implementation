<?php


class new_order {

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
	public function new_order_customer_by_id($w_id, $d_id, $c_id) {
		// Get customer based on last name
		$get_customers = 'SELECT C_W_ID, C_DISCOUNT, C_LAST, C_CREDIT'.
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
	
	public function new_order_transaction() {
		// Set up data
		$w_id = 0;
		$d_id = rand(0,9);
		$c_id = rand(0, 2999);
		
		$ol_cnt = rand(5,15);
		$item_input = array();
		for($i = 0; $i < $ol_cnt; $i++) {
			$i_id = rand(0,99999);
			$ol_supply_w_id = 0;
			$ol_quantity = rand(1,10);
			$item_input[$i] = array(
				'I_ID'=>$i_id,
				'OL_SUPPLY_W_ID'=>0,
				'OL_QUANTITY'=>$ol_quantity
			);
		}
			
		$out = array();
		try {			
			// Keep track of transaction duration
			$start_time = microtime(true);

			// Begin transaction
			$this->pdo->beginTransaction();
			
			// Set auto commit
			$this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT,0);
			
			
			// Customer warehouse vs. not
			$x_rand = rand(1,100);
			if($x_rand == 1) { $w_id = rand(1,10);	}
			// Else, keep input w_id
			
			
			// Get warehouse information
			$get_warehouse = "SELECT W_TAX FROM warehouse WHERE W_ID =:w_id; ";
			$stmt = $this->pdo->prepare($get_warehouse);
			$stmt->execute(array(":w_id" => $w_id));
			if($stmt == false) { echo "No warehouse found by id."; }
			$result = $stmt->fetchColumn();
			$warehouse_info = $result;

			
			// Get district information
			$get_district = "SELECT D_TAX, D_NEXT_O_ID".
							" FROM district ".
							" WHERE D_W_ID =:w_id".
							" AND D_ID =:d_id; ";
			$stmt = $this->pdo->prepare($get_district);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id));
			if($stmt == false) { echo "No district found by id"; }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			if(sizeof($result) == 0) { $this->pdo->rollBack(); return $out; }
			$district_info = $result[0];
			
			
			// Update district D_NEXT_O_ID
			$update_district = "UPDATE district
								SET D_NEXT_O_ID = :next
							WHERE D_ID = :did; ";
			$stmt = $this->pdo->prepare($update_district);
			$stmt->execute(array(":next" => ($district_info['D_NEXT_O_ID'] + 1), ":did" => $d_id));
			if($stmt == false) { echo "District update failed."; }
			
			
			
			// Get customer information
			$customer_info = $this->new_order_customer_by_id($w_id, $d_id, $c_id);
			if(sizeof($customer_info) == 0) { $this->pdo->rollBack(); return $out; }



			// Set attibute for if local order
			if($customer_info["C_W_ID"] == $w_id) {
				$all_local = 1;
			} else {
				$all_local = 0;
			}
			
			
			
			// Insert order table
			$date = date("y-m-d");
			$insert_order = "INSERT INTO orders (O_ID, O_D_ID, O_W_ID, O_C_ID, O_ENTRY_D, O_OL_CNT, O_ALL_LOCAL)".
					" VALUES(:o_id, :d_id, :w_id, :c_id, :date, :ol_cnt, :all_local); ";
			$stmt = $this->pdo->prepare($insert_order);
			$stmt->execute(array(":o_id"=>$district_info["D_NEXT_O_ID"], ":d_id" => $d_id, ":w_id" => $w_id, ":c_id"=>$c_id, ":date"=>$date, ":ol_cnt"=>sizeof($item_input), ":all_local"=>$all_local));
			if($stmt == false) { echo "No customer found by id"; }
			
			
			
			// Insert new order table
			$insert_new_order = " INSERT INTO new_order (NO_O_ID, NO_D_ID, NO_W_ID)".
							" VALUES(:o_id, :d_id, :w_id); ";
			$stmt = $this->pdo->prepare($insert_new_order);
			$stmt->execute(array(":o_id"=>$district_info["D_NEXT_O_ID"],":d_id" => $d_id, ":w_id" => $w_id));
			if($stmt == false) { echo "Insert to new order failed."; $this->pdo->rollBack(); return $out; }
			
			
			
			// For each ol_cnt
			$i = 1;
			$total_amount = 0;
			$item_info = array();
			$stock_info = array();
			foreach($item_input as $item) {

				// Get item information from input array
				$i_id = $item["I_ID"];
				$ol_supply_w_id = $item["OL_SUPPLY_W_ID"];
				$ol_quantity = $item["OL_QUANTITY"];		
				
				// Select item
				$select_item = "SELECT I_ID, I_PRICE, I_NAME, I_DATA".
								" FROM item".
								" WHERE I_ID =:i_id; ";
				$stmt = $this->pdo->prepare($select_item);
				$stmt->execute(array(":i_id" => $i_id));
				if($stmt == false) { echo "No item found by id"; }
				$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				if(sizeof($result) == 0) { $this->pdo->rollBack(); return $out; }
				$item_info[$i] = $result[0];				
				
				if($d_id > 9) { $s_district = "S_DISTR_" . $d_id; } // Create the column name for the stock district
				else { $s_district = "S_DISTR_0" . $d_id; }
				
				// Select stock information
				$select_stock = "SELECT S_YTD, S_ORDER_CNT, S_REMOTE_CNT, S_QUANTITY, S_DATA, :s_distr".
								" FROM stock".
								" WHERE S_I_ID =:i_id".
								" AND S_W_ID =:w_id; ";
				$stmt = $this->pdo->prepare($select_stock);
				$stmt->execute(array(":s_distr"=> $s_district, ":i_id" => $i_id, ":w_id"=> $w_id));
				if($stmt == false) { echo "No stock found by id"; }
				$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				if(sizeof($result) == 0) { $this->pdo->rollBack(); return $out; }
				$stock_info[$i]  = $result[0];
				
				
				$temp_stock = $stock_info[$i]["S_QUANTITY"];
				// Check if s_quantity < ol_quantity
				if(($temp_stock - $ol_quantity) >= 10) {
					// stock = stock - ol_quantity
					$temp_stock = $stock_info[$i]["S_QUANTITY"] - $ol_quantity;
				} else {
					// Stock = (stock - ol_quantity) + 91
					$temp_stock = ($stock_info[$i]["S_QUANTITY"] - $ol_quantity) + 91;
				}
				$stock_info[$i]["S_YTD"] =+ $ol_quantity; // Increase YTD
				$stock_info[$i]["S_ORDER_CNT"] += 1; // Increment number of orders
				if($all_local == 0) {
					$stock_info[$i]["S_REMOTE_CNT"] += 1; // If remote then incremement
				}
				$amount = $ol_quantity * $item_info[$i]["I_PRICE"];
				$i_data = $item_info[$i]["I_DATA"];
				$s_data = $stock_info[$i]["S_DATA"];
				if(strpos($i_data, "ORIGINAL") !== false && strpos($s_data, "ORIGINAL") !== false) {
					$stock_info[$i]['B/G'] = "B";
				} else {
					$stock_info[$i]['B/G'] = "G";
				}
				
				// Insert order line
				$insert_order_line = " INSERT INTO order_line(OL_O_ID, OL_D_ID, OL_W_ID, OL_NUMBER, OL_I_ID, OL_SUPPLY_W_ID, OL_QUANTITY, OL_AMOUNT, OL_DIST_INFO)".
						" VALUES(:o_id, :d_id, :w_id, :ol_num, :i_id, :ol_supply_w_id, :ol_quantity, :ol_amount, :ol_dist_info); ";
				$stmt = $this->pdo->prepare($insert_order_line);
				$stmt->execute(array(":o_id"=> $district_info['D_NEXT_O_ID'], ":d_id" => $d_id, ":w_id"=> $w_id, ":ol_num"=>$i, ":i_id"=>$i_id, ":ol_supply_w_id"=>$w_id, ":ol_quantity"=>$ol_quantity, ":ol_amount"=>$amount, ":ol_dist_info"=>$stock_info[$i][$s_district]));
				if($stmt == false) { echo "Insert to order line failed."; $this->pdo->rollBack(); return $out; }				
				
				$total_amount += $amount;
				
				$i++; // increment order number
				
			}	
			
			
			// RBK
			$rbk = rand(1,100);
			if($rbk == 1) { 
			
				//$customer_error = $this->new_order_customer_by_id($w_id, $d_id, $c_id);
				
				$this->pdo->rollBack(); 
				
				// Keep track of transaction duration
				$end_time = microtime(true);
				$out = array(
					"tt"=>(($end_time - $start_time) * 1000),
					"c"=>$customer_info,
					"check"=>1
				);

				return $out;
			} // Fails 1% of the time
			
			
			// Total purchase amount from customer
			$total_amount = $total_amount * (1 - $customer_info["C_DISCOUNT"]) * (1+$warehouse_info+$district_info["D_TAX"]);	
			
			// Compile information
			$out = array(
				"w"=>$warehouse_info,
				"d"=>$district_info,
				"c"=>$customer_info,
				"i"=>$item_info,
				"a"=>$total_amount,
				"s"=>$stock_info,
				"check"=>0
			);
			
			// commit the transaction
			$this->pdo->commit();
			
			// Keep track of transaction duration
			$end_time = microtime(true);
			$out["tt"] = ($end_time - $start_time) * 1000;
			
			return $out;
			
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			die($e->getMessage());
		}	
	}
	
}// END CLASS

// ------------------------------------ test the new_order transaction ------------------------------------
	$new_order_obj = new new_order();
	$new_order_obj->construct();
	
	for($j = 1; $j<= 10; $j++) {
		$total = 0;
		for($i = 1; $i <= 100; $i++) {
			$output = $new_order_obj->new_order_transaction();
			if(isset($output['tt'])) {
				$total += $output['tt'];
			}
		}
		$total_done = $total / 100;
		echo $total_done . "<br>";
	}
	
	$new_order_obj->destruct();

	
// ------------------------------------ test the new_order transaction ------------------------------------
