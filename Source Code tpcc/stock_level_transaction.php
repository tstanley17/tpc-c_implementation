<?php

class stock_level {

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
	
	public function stock_level_transaction($w_id, $d_id) {
		try {
			// Begin transaction
			$this->pdo->beginTransaction();
			
			// Set threshhold
			$threshhold = 20; 										// HAVE TO DECIDE VALUE
			
			// Get district information
			$get_district_info = "SELECT D_NEXT_O_ID FROM district".
									" WHERE D_W_ID =:w_id".
									" AND D_ID =:d_id; ";
			$stmt = $this->pdo->prepare($get_district_info);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id));
			if($stmt == false) { echo "No district information retreived."; $this->pdo->rollBack(); }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$district_info = $result[0];
			
			// Get 20 order-line rows
			$get_order_line_info = "SELECT * FROM order_line".
									" WHERE OL_W_ID =:w_id".
									" AND OL_D_ID =:d_id".
									" AND OL_O_ID >=:o_id";
			$stmt = $this->pdo->prepare($get_order_line_info);
			$stmt->execute(array(":w_id" => $w_id, ":d_id" => $d_id, ":o_id"=>($district_info["D_NEXT_O_ID"])));
			if($stmt == false) { echo "No order-line information retreived."; $this->pdo->rollBack(); }
			$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			$order_line_info = $result;
			
			// Get stock information
			$get_stock_info = "SELECT COUNT(S_QUANTITY) FROM stock".
								" WHERE S_I_ID =:i_id".
								" AND S_W_ID =:w_id".
								" AND S_QUANTITY < :threshhold; ";
			$stock_amt = array();
			$i = 0;
			foreach($order_line_info as $id) {
				$stmt = $this->pdo->prepare($get_stock_info);
				$stmt->execute(array(":i_id" => $id["OL_I_ID"], ":w_id" => $w_id, ":threshhold"=>$threshhold));
				if($stmt == false) {
					continue;
				} else {
					$stock_amt[$i] = $stmt->fetchColumn();
					$i++;
				}
			}
			
			$total_under_threshhold = 0;
			foreach($stock_amt as $item) {
				$total_under_threshhold += $item;
			}
			
			// commit the transaction
			$this->pdo->commit();
			$out = array(
				"w"=>$w_id,
				"d"=>$d_id,
				"threshhold"=>$threshhold,
				"total_under"=>$total_under_threshhold
			);
			return $out;
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			die($e->getMessage());
		}	
	}
	
	public function display_stock_level($in) {
		echo "<style>";
		include 'webStyles.css';
		echo "</style>";
		
		echo "<table class='formInput'>".
			"<tr><th>Warehouse: ".$in['w']."</th><th>Stock-Level District: ".$in['d']."</th></tr>".
			"<tr><th>Stock Level Threshhold: ".$in['threshhold']."</th></tr>".
			"<tr><th>Low stock: ".$in['total_under']."</th></tr>";
			echo "</table>";
		
		echo "<br><br><br>";
		echo "<form action='newOrderInput.php'><input style='width: 150px;' type='submit' value='Back'></form>";
	}

}



// ------------------------------------ test the payment transaction ------------------------------------

	$stock_level_obj = new stock_level();
	$stock_level_obj->construct();
	$committed = $stock_level_obj->stock_level_transaction(0,rand(0,9));
	$stock_level_obj->destruct();
	if(!empty($committed)){
		$stock_level_obj->display_stock_level($committed);
	} else {
		echo "Order status retrieval was unsuccessful.";
	}
	
	
// ------------------------------------ test the payment transaction ------------------------------------



