<?php
    require "header.php";


	echo "	<main>
			  <div class=\"wrapperMain\">
				<section class=\"sectionDefault\">
					
				  <h1 class=\"formInput\">Order Input</h1>
				 
				  <form class=\"formInput\" action=\"new_order_transaction.php\" method=\"post\">
						<p> Select Warehouse: </p><input type=\"number\" name=\"warehouseNumber\" placeholder=\"Warehouse Number\">
						<p> Select District: </p><input type=\"number\" name=\"districtNumber\" placeholder=\"District Number\">
						<p> Select Customer: </p><input type=\"number\" name=\"customerNumber\" placeholder=\"Customer Number\">
						<body>
							<div class=\"tableInput\"> 
								<table class=\"centerTable\">
									<tr>
										<th>OL_I_ID</th>
										<th>OL_Supply_W_ID</th>
										<th>OL_Quanity</th>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[0]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[0]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[0]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[1]\" placeholder=\"Item Quanity\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[1]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[1]\" placeholder=\"Item Quanity\"> </td>
									</tr>       
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[2]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[2]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[2]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[3]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[3]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[3]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[4]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[4]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[4]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[5]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[5]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[5]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[6]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[6]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[6]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[7]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[7]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[7]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[8]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[8]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[8]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[9]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[9]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[9]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[10]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[10]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[10]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[11]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[11]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[11]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[12]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[12]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[12]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[13]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[13]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[13]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									<tr>
										<td> <input type=\"number\" name=\"OL_I_ID[14]\" placeholder=\"Item Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Supply_W_ID[14]\" placeholder=\"Supplying Warehouse Number\"> </td>
										<td> <input type=\"number\" name=\"OL_Quanity[14]\" placeholder=\"Item Quanity\"> </td>
									</tr>
									
								</table>
							</div>
						</body>
						
						<button type=\"submit\" name=\"orderSubmit\">Submit</button>

				  </form>
					<form action='delivery_transaction.php'><input type='submit' value='Delivery'></form>
					<form action='payment_transaction.php'><input name=\"orderSubmit\" type='submit' value='Payment'></form>
					<form action='stock_level_transaction.php'><input name=\"orderSubmit\" type='submit' value='Stock Level'></form>
					<form action='new_order_transaction_random.php'><input name=\"orderSubmit\" type='submit' value='New Order Testing'></form>
				</section>
			  </div>
			</main>";
?>