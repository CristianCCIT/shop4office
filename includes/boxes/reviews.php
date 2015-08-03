<?php
	/************************************************************************\
	 * 		Reviews System for osC & ABOCMS				                    *
	 *	Used Tables and fields:							                    *
	 *		reviews										                    *
	 *			| reviews_id | reviewer_id | products_model | date_added	*
	 *		review_description								                *
	 *			| reviews_id | reviews_text | value     					*
     *      review_to_customers                                             *
     *          | reviewer_id | customer_id | r_username | r_mailaddress    *
	 *		customers								 	                    *
	 *			| customers_id | customers_firstname 	                    *
	 *		products									                    *
	 *			| products_model |						                    *
	\************************************************************************/
	
	$postreview = false;
	$isproduct = false;
	$allowrev = true;
	
	// Check if there was data posted.
		
	if (isset($_POST['r_text']) && $_POST['r_text'] != '') { $postreview = true; }	
	if (isset($_POST['rating']) && $_POST['rating'] != '') { $postreview = true; }	
	
	if (isset($_GET['products_id']) && $_GET['products_id'] != '') {	
		$isproduct = true;
		$product['id'] = $_GET['products_id'];
	} else {	
		$product['id'] = '';
        $isproduct = false;
	}
	
	if ($postreview) {	
	
		$allowrev = false;
		$temp = $_POST;
		
		if (postReview($temp)) {
			$error = false;		
			$allowrev = false;
		} else {		
			$error = true;
			$allowrev = true;
		}	
	}
	

	//This is a product page so reviews are possible
	if ($isproduct) {
	
	
		//if (ONE_REVIEW_AT_A_TIME == 'true') {
	
		/****************************************************\
         *	Check cookie if this article has been	        *
		 *	reviewed yet. If it has, don't allow 			*
		 *	a new review.									*
		\****************************************************/

        /*     tep_session_is_registered()

		if (isset($_SESSION['REV'])) {
		
			if (in_array($product['id'], $_SESSION['REV'])) {

				$allowrev = false;

			} else {

                $allowrev = true;
            }
		
		} else { $REV = array(); tep_session_register($REV); }
	
		
		
		} else {
		
			if (ONE_REVIEW_PER_SESSION == 'true') {

			
			} else {


			}
		}*/
	
		$sql = "SELECT * FROM products WHERE products_id = ".$product['id'];
				
		if ($query = tep_db_query($sql)) {	
		
			$result = tep_db_fetch_array($query);			
			$product['model'] = $result['products_model'];
			
			if ($product['model'] != '' && $product['model'] != ' ') {

			$sql = "SELECT * FROM `reviews` WHERE `products_model` = '".$product['model']."' ORDER BY date_added DESC ";
				if ($query = tep_db_query($sql)) {

                    $reviews = array();

					while ($result = tep_db_fetch_array($query)) {					
						$reviews[] = $result;					
					}
					?>
					<div id="review-wrapper">
					<h2><?php echo Translate('Reviews'); ?></h2>
					
					<?php
					foreach ($reviews as $block) {

						$review['id'] = $block['reviews_id'];
						$review['author_id'] = $block['reviewer_id'];
						$review['date'] = $block['date_added'];

                        $sql = "SELECT * FROM `reviews_description` WHERE `reviews_id` = ".$review['id'];
                        $query = tep_db_query($sql);
                        $result = tep_db_fetch_array($query);

						$review['text'] = stripslashes($result['reviews_text']);
						$review['value'] = $result['value'];

                        // Get contact info

                        if (isset($review['author_id']) && $review['author_id'] != '') {

                            $sql = "SELECT * FROM `reviews_to_customers` WHERE `reviewer_id` = ".$review['author_id'];
                            $query = tep_db_query($sql);
                            $result = tep_db_fetch_array($query);

                            if (isset($result['customer_id']) && $result['customer_id'] != 0) {

                                $sql = "SELECT * FROM `customers` WHERE `customers_id` = ".$result['customer_id'];
                                $query = tep_db_query($sql);
                                $result = tep_db_fetch_array($query);

                                $customer['firstname'] = $result['customers_firstname'];
                                $customer['mailto'] = 'mailto:'.$result['customers_email_address'].'?subject=Uw review op '.STORE_NAME;

                            } elseif(isset($result['r_username']) && $result['r_username'] != '') {

                                $customer['firstname'] = $result['r_username'];
                                $customer['mailto'] = 'mailto:'.$result['r_mailaddress'].'?subject=Uw review op '.STORE_NAME;

                            } else {

                                // A reviewer ID was set, yet there is no info to be found in the row.

                                $customer['firstname'] = REVIEW_STANDARD_USERNAME;
                                $customer['mailto'] = '';
                            }

                        } else {

                            // No reviewer ID was assigned. This review was posted without isername or email-address.

                            $customer['firstname'] = REVIEW_STANDARD_USERNAME;
                            $customer['mailto'] = '';

                        }


                        //Build a review box for every review
						buildReview($customer['firstname'], $customer['mailto'], $review['text'], $review['value']);
					}

					if (count($reviews) <= 0 ) {
					
						?>
							<p><?php echo Translate('Er zijn nog geen reviews geschreven voor dit product.'); ?></p>
							<h3><?php echo Translate('Schrijf als eerste een review!'); ?></h3>
						
						<?php 
					}
					
					// Get data for logged in user.
					
					if (tep_session_is_registered('customer_id')) {
					
						$customer['id'] = $_SESSION['customer_id'];						
						$sql = "SELECT * FROM `customers` WHERE `customers_id` = ".$customer['id'];
						$query = tep_db_query($sql);
						$result = tep_db_fetch_array($query);
						
						$customer['name'] = $result['customers_firstname'];
						$customer['email'] = $result['customers_email_address'];						
					}					
					?>				
					
						<div class="comments">
						<?php if (count($reviews) > 0 ) { ?><h3><?php if ($allowrev) {echo Translate ('Schrijf een beoordeling');} else { echo Translate ('Bedankt voor uw beoordeling');}  ?></h3> <?php } ?>
						<?php /* START COMMENTS */	
						
						if ($allowrev) {
						if (REVIEW_LOGGED_IN != 'true') { // true = login required, false = login not required.
						
							if (tep_session_is_registered('customer_id')) {	// Comment field when a user is logged in.
					?>		
							<h3>Ingelogd als&nbsp;<?php echo $customer['name']; ?> </h3>
							<form method='POST' action="<?php echo $_SESSION['PHP_SELF']; ?>"> 
							<table>
								<?php if (USE_STAR_RATING == 'true') { ?>
								<tr><td><label for="rating"><?php echo Translate('Geef een score:'); ?></label></td>
								<td>
									<input name="rating" type="radio" class="rating-star" value="1"/>
									<input name="rating" type="radio" class="rating-star" value="2"/>
									<input name="rating" type="radio" class="rating-star" value="3"/>
									<input name="rating" type="radio" class="rating-star" value="4"/>
									<input name="rating" type="radio" class="rating-star" value="5"/>
								</td>
								</tr>
								<?php } ?>
								<?php if (USE_TEXT_REVIEW == 'true') { ?>
								<tr>
									<td colspan="2"><textarea name="r_text" id="r_text" cols="45" rows="5"></textarea></td>
								</tr>	
								<?php } ?>
                                <tr class="honey">
                                    <td><input type="text" id="language" name="language" value="" ></td>
                                    <td><input type="text" id="timezone" name="timezone" value="" ></td>
                                </tr>
                                <tr class="honey">
                                    <td colspan="2"><input type="text" id="comment" name="comment" value="" ></td>
                                </tr>
								<tr>
									<td>
										<input type="hidden" id="custid" name="customer_id" value="<?php echo $_SESSION['customer_id'] ?>" />
										<input type="hidden" id="prodid" name="product_id" value="<?php echo $product['id']; ?>" />
									</td>
									<td>
										<input name="submit" type="submit" value="<?php echo Translate('Verzenden');?>" />
									</td>	
								</tr>
							</table>	
							</form>
						
						<?php
							} else { // Comment fields when a user isn't logged in.
						?>
													
							<form method='POST' action="<?php echo $_SESSION['PHP_SELF']; ?>"> 
								<table>
								<?php if (ASK_FOR_IDENTIFY == 'true') { ?>								
									<tr><td><label for="name">Naam:</label></td><td><input type="text" name="name" id="name" /></td></tr>
									<tr><td><label for="email">Email:</label></td><td><input type="text" name="email" id="email" /></td></tr>
								<?php } ?>							
								<?php if (USE_STAR_RATING == 'true') { ?>
								<tr><td><label for="rating"><?php echo Translate('Geef een score:'); ?></label></td>
								<td>
									<input name="rating" type="radio" class="rating-star" value="1"/>
									<input name="rating" type="radio" class="rating-star" value="2"/>
									<input name="rating" type="radio" class="rating-star" value="3"/>
									<input name="rating" type="radio" class="rating-star" value="4"/>
									<input name="rating" type="radio" class="rating-star" value="5"/>
								</td>
								</tr>
								<?php } ?>
								<?php if (USE_TEXT_REVIEW == 'true') { ?>
								<tr><td colspan="2">
									<textarea name="r_text" id="r_text" cols="45" rows="5"></textarea>
								</td></tr>	
								<?php } ?>
                                <tr class="honey">
                                    <td><input type="text" id="language" name="language" value="" ></td>
                                    <td><input type="text" id="timezone" name="timezone" value="" ></td>
                                </tr>
                                <tr class="honey">
                                    <td colspan="2"><input type="text" id="comment" name="comment" value="" ></td>
                                </tr>
								<tr>

									<td><input type="hidden" id="prodid" name="product_id" value="<?php echo $product['id']; ?>" /></td>
									<td><input name="submit" type="submit" value="<?php echo Translate('Verzenden');?>" /></td>
								</tr>	
								</table>
							</form>
							
						<?php	
							}
									
						} elseif (tep_session_is_registered('customer_id')) { // Log in required and user is logged in	
						?>	
							<h3>Ingelogd als&nbsp;<?php echo $customer['name']; ?> </h3>
							<form method='POST' action="<?php echo $_SESSION['PHP_SELF']; ?>"> 
							<table>
								<?php if (USE_STAR_RATING == 'true') { ?>
								<tr><td><label for="rating"><?php echo Translate('Geef een score:'); ?></label></td>
								<td>
									<input name="rating" type="radio" class="rating-star" value="1"/>
									<input name="rating" type="radio" class="rating-star" value="2"/>
									<input name="rating" type="radio" class="rating-star" value="3"/>
									<input name="rating" type="radio" class="rating-star" value="4"/>
									<input name="rating" type="radio" class="rating-star" value="5"/>
								</td>
								</tr>
								<?php } ?>
								<?php if (USE_TEXT_REVIEW == 'true') { ?>
								<tr>
									<td colspan="2"><textarea name="r_text" id="r_text" cols="45" rows="5"></textarea></td>
                                </tr>
								<?php } ?>
                                <tr class="honey">
                                    <td><input type="text" id="language" name="language" value="" ></td>
                                    <td><input type="text" id="timezone" name="timezone" value="" ></td>
                                </tr>
                                <tr class="honey">
                                    <td colspan="2"><input type="text" id="comment" name="comment" value="" ></td>
                                </tr>
								<tr>
									<td>
										<input type="hidden" id="custid" name="customer_id" value="<?php echo $_SESSION['customer_id'] ?>" />
										<input type="hidden" id="prodid" name="product_id" value="<?php echo $product['id']; ?>" />
									</td>
									<td>
										<input name="submit" type="submit" value="<?php echo Translate('Verzenden');?>" />
									</td>	
								</tr>
							</table>	
							</form>
									
								<?php
						} else {

							echo Translate('U moet ingelogd zijn om een beoordeling te plaatsen!');

						}	/* END COMMENTS */ 
						
						}
						?>				
						
						</div>
					</div>
										
					<?php
				 
				} else { die ('[ERROR] Official description: ' . mysql_error()); }
				
								
				
			} else {			
				/********************************************************************************************************\ 
				 * 	Without a product model, there isn't a fixed ID to which we can assign reviews.						*
				 * 	The product_id can change, since this only depends on the product being entered into the database.	*
				\********************************************************************************************************/ 
				
				if (REVIEWS_BY_ID != 'false') {
					// The setting Reviews by id allows for reviews based on the product ID. This is discouraged and not supported yet.



				
				} else {
					// No reviews will be posted.
				}				
			}
			
		} else { die ('[ERROR] Official description: ' . mysql_error()); } // Could not query database.
		
	} else { /* Not a products page, so no reviews */ }
	
	
	/********************************************************\
	 *	Builds a review in standard form. 					*
	 *	r_name : Name of the person that wrote the review   *
	 *	r_mail : Not used yet. e-mail address of the person *
	 *	r_text : The review itself							*
	 *	r_val  : An INT from 1 - 5							*
	 *	Function does not return anything, it simply prints	*
	 *	the review structure								*
     * @return Filled in review template                    *
	\********************************************************/
	function buildReview($r_name, $r_email, $r_text, $r_val) {

        $root = getcwd();
        $path = $root .STS_TEMPLATE_DIR. 'reviewtemplate.html';

        $tags = array(
            'author' => &$r_name,
            'rating' => &$rating,
            'text' => &$r_text,
            'email' => &$r_email
        );

        if (USE_STAR_RATING == 'false' && $r_text == '') { return null; }
        if (USE_TEXT_REVIEW == 'false' && (int)$r_val <= 0) { return null; }

        $rating = '<ul>';
        for ($i = 1; $i <= (int)$r_val; $i++) {
            $rating .= "<li>&#9733;</li>";
        }
        $rating .= '</ul>';

        if (file_exists($path)) {
            $file = file_get_contents($path);

            foreach ($tags as $tag => $content) {
                $tag = '$'.$tag.'$';
                $file = str_replace($tag, $content, $file);
            }

            echo $file;

        } else {
            // Standard review layout.

        ?>

            <div class="review">
                <?php if (DISPLAY_REVIEW_NAME == 'true') { ?>
                <h3>Door <?php echo $r_name; ?></h3>
                <?php } ?>
                <?php if (USE_STAR_RATING == 'true') { ?>
                <div class="review-rating">
                    <h4>Rating:</h4>
                    <?php echo $rating; ?>
                </div>
                <?php } ?>
                <?php if (USE_TEXT_REVIEW == 'true') { ?>
                <div class="review-comment"><p><?php echo $r_text; ?></p></div>
                <?php } ?>
            </div>

        <?php




        }
	}	
	
	/************************************************************\
	 *	postReview is supplied with an array of data			*
	 *	that should be inserted into the database				*
	 *															*
	 *	It checks which fields are filled in and used, empty 	*
	 * 	fields are filled in with standards						*
	 *															*
	 * 	@return true if successful, array when fails.			*
	\************************************************************/
	
	function postReview($vars) {

		$known = false;
		$rating = false;
		$text = false;
		$pseuname = false;
		$pseumail = false;

        if ((isset($vars['timezone']) && $vars['timezone'] != '') || (isset($vars['language']) && $vars['language'] != '') || (isset($vars['comments']) && $vars['comments'] != '' )) {

            die("Hello, you've reached this page due to a honeypot trap. If you are not a robot, and simply wanted to post a review, go back and turn of the auto fill-in of forms in your browser. It will stop this from happening");

        }

        foreach ($vars as $key=>$var) {
            $vars[$key] = tep_db_prepare_input(addslashes($var));
        }

		if (isset($vars['customer_id'])) { $known = true; $customer['id'] = $vars['customer_id'];}
		if (isset($vars['name']) && $vars['name'] != '' ) { $pseuname = true; }
		if (isset($vars['email']) && $vars['email'] != '') { $pseumail = true; }

        $product['id'] = $vars['product_id'];
        $review['value'] = $vars['rating'];
        $review['text'] = $vars['r_text'];

        $sql = "SELECT `products_model` FROM `products` WHERE `products_id` = ".$product['id'];
        $query = tep_db_query($sql);
        $result = tep_db_fetch_array($query);
        $product['model'] = $result['products_model'];

		if ($known) { // Reviewing as a known user.

            $sql = "SELECT * FROM reviews_to_customers WHERE customer_id = ".$customer['id'];
            $query = tep_db_query($sql);

            if (tep_db_num_rows($query) > 0) {
                // This user has already been assigned a custom reviewer_id for reviews.
                $result = tep_db_fetch_array($query);
                $customer['review_id'] = $result['reviewer_id'];

            } else {
                // This user has not yet been assigned a custom reviewer_id for reviews.

                $sql = "INSERT INTO reviews_to_customers (customer_id) VALUES (".$vars['customer_id'].")";
                $query = tep_db_query($sql);
                $customer['review_id'] = mysql_insert_id();
            }

            // Insert the info into the database.

            $sql = "INSERT INTO reviews (reviewer_id, products_model) VALUES (".$customer['review_id'].", '".$product['model']."')";
            $query = tep_db_query($sql);
            $review['id'] = mysql_insert_id();

            $sql = "INSERT INTO `reviews_description` (`reviews_id`, `reviews_text`, `value`) VALUES ( ".$review['id'].", '".$review['text']."', '".$review['value']."');";
            $query = tep_db_query($sql);

            if (!$query) { die('Could not post the review into the database: '.mysql_error()); }

        } elseif ($pseuname) { // Reviewing as a guest

            $sql = "SELECT * FROM reviews_to_customers WHERE r_username = '".$vars['name']."'";
            $query = tep_db_query($sql);

            if (tep_db_num_rows($query) > 0) {
                //username already excists. Check for same email adress

                if ($pseumail) {

                    $sql = "SELECT * FROM reviews_to_customers WHERE r_username = '".$vars['name']."' AND r_mailaddress = '".$vars['email']."'";
                    $query = tep_db_query($sql);

                    if (tep_db_num_rows($query) > 0) {
                        // This user has posted before.

                        $result = tep_db_fetch_array($query);
                        $customer['review_id'] = $result ['reviewer_id'];

                        $sql = "INSERT INTO reviews (reviewer_id, products_model) VALUES (".$customer['review_id'].", '".$product['model']."')";
                        $query = tep_db_query($sql);
                        $review['id'] = mysql_insert_id();

                        $sql = "INSERT INTO `reviews_description` (`reviews_id`, `reviews_text`, `value`) VALUES ( ".$review['id'].", '".$review['text']."', '".$review['value']."');";
                        $query = tep_db_query($sql);

                        if (!$query) { die('Could not post the review into the database: '.mysql_error()); }

                    } else {
                        // Duplicate usernames. -> Return to page and say username is already in use.

                    }

                } else {
                    // No mail has been supplied. We can't tell if duplicate user or not. So let's say it isn't.

                    $result = tep_db_fetch_array($query);
                    $customer['review_id'] = $result ['reviewer_id'];

                    $sql = "INSERT INTO reviews (reviewer_id, products_model) VALUES (".$customer['review_id'].", '".$product['model']."')";
                    $query = tep_db_query($sql);
                    $review['id'] = mysql_insert_id();

                    $sql = "INSERT INTO `reviews_description` (`reviews_id`, `reviews_text`, `value`) VALUES ( ".$review['id'].", '".$review['text']."', '".$review['value']."');";
                    $query = tep_db_query($sql);

                    if (!$query) { die('Could not post the review into the database: '.mysql_error()); }
                }
            } else {
                // username does not yet exist.

                if ($pseumail) {
                    // Mail is supplied
                    $sql = "INSERT INTO reviews_to_customers (r_username, r_mailaddress) VALUES ('".$vars['name']."', '".$vars['email']."')";

                } else {
                    // Mail isn't supplied
                   $sql = "INSERT INTO reviews_to_customers (r_username) VALUE ('".$vars['name']."')";
                }

                $query = tep_db_query($sql);
                if (!$query) { die ('MYSQL ERROR'); }

                $customer['review_id'] = mysql_insert_id();

                // Insert the info into the database.

                $sql = "INSERT INTO reviews (reviewer_id, products_model) VALUES (".$customer['review_id'].", '".$product['model']."')";
                $query = tep_db_query($sql);
                $review['id'] = mysql_insert_id();

                $sql = "INSERT INTO `reviews_description` (`reviews_id`, `reviews_text`, `value`) VALUES ( ".$review['id'].", '".$review['text']."', '".$review['value']."');";
                $query = tep_db_query($sql);

                if (!$query) { die('Could not post the review into the database: '.mysql_error()); }
            }

        } else {
            //Reviewing sans name.
            // Check if we allow this

            if (ALLOW_FULL_ANONYMOUS == 'true') {
                // It is allowed to post without any user info.

                $sql = "INSERT INTO reviews (products_model, reviewer_id) VALUE ('".$product['model']."', NULL)";
                $query = tep_db_query($sql);
                $review['id'] = mysql_insert_id();

                $sql = "INSERT INTO `reviews_description` (`reviews_id`, `reviews_text`, `value`) VALUES ( ".$review['id'].", '".$review['text']."', '".$review['value']."');";
                $query = tep_db_query($sql);

                if (!$query) { die('Could not post the review into the database: '.mysql_error()); }

            } else {
                // Not allowed -> Return to page and ask for username


            }
        }
    }


?>
