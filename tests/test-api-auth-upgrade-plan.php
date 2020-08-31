<?php
use Wubtitle\Api\ApiAuthUpgradePlan;
/**
 * Class TestApiLivenseValidation
 *
 * @package Wubtitle
 */

 /**
	* Test callback endpoint.
	*/
class TestApiAuthUpgradePlan extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function SetUp(){
		parent::setUp();
		$this->instance = new ApiAuthUpgradePlan;
	}
	/**
	 * Test endpoint callback auth plan
	 */
	 public function test_return_plan(){
        $all_plans = array(
            array(
                'stripe_code' => 'id_1',
            ),
            array(
                'stripe_code' => 'id_2',
            ),
            array(
                'stripe_code' => 'id_3',
            ),
        );
        update_option( 'wubtitle_all_plans', $all_plans );
        update_option( 'wubtitle_wanted_plan_rank', 2 );
        $expected_response = array(
            'data' => array(
                'plan_code' => $all_plans[2]['stripe_code']
            )
        );
		$response          = $this->instance->return_plan();
		//Check callback response
		$this->assertEqualSets($expected_response, $response);
     }
     /**
      * Test reactivate plan
      */
      public function test_reactivate_plan() {
          $expected_response = array(
              'data' => array(
                  'is_reactivating' => true,
              )
          );
          update_option( 'wubtitle_is_reactivating', true );
          $response = $this->instance->reactivate_plan();
          //Check callback response
          $this->assertEqualSets($expected_response, $response);
      }
}