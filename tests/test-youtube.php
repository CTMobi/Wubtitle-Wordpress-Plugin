<?php
/**
 * Test YouTube
 *
 * @package Ear2Words
*/

use \Ear2words\Core\Sources\YouTube;

/**
* Test ricezione trascrizione.
*/
class TestYoutubeTranscript extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function SetUp(){
		parent::setUp();
		$this->instance = new YouTube();
	}
	/**
	 * Test callback dell'endpoint che riceve i job falliti.
	 */
	 public function test_get_subtitle(){

		$expected_text = "per quanto riguarda la situazione dell'italia io penso che noi tutti sappiamo che l'italia è stata colpita da questa pandemia in maniera molto molto dura dal primo paese a dover decidere di misure dure e le misure di lockdown sono iniziate prima in altri paesi e le conseguenze per l'economia sono state drastiche allo stesso tempo come avete visto nelle nostre previsioni diamo una ripresa abbastanza forte nella seconda metà dell'anno e anche nel 2021 io penso che le misure prese dal governo contribuiscano ad andare in questa direzione e penso che sia il governo a dover decidere sulla linea di credito rafforzata della sm noi come commissione ci occupiamo solo dell'ammissibilità e l'ammissibilità come sapete verrà comunicata domani all'eurogruppo a tutti gli stati membri e poi saranno gli stati membri che dovranno decidere se utilizzare questa linea di credito e come sapete in italia c'è un dibattito sull'argomento ma non è la commissione che deve dare il suo parere per quanto riguarda la corte costituzione tedesca la sentenza di calcio e dunque come appena detto mamma dopo aver sentito il selezione di con abbiamo parlato al collegio che ci sono due punti molto chiari noi ribadiamo il primato del diritto europeo le sentenze della corte di giustizia europea sono vincolanti per tutti i tribunali nazionali e in secondo luogo noi abbiamo sempre rispettato e sosteniamo appieno l'indipendenza della bce per quanto riguarda l'attuazione delle politiche monetarie poi studieremo questa sentenza nazionale in maniera più dettagliata per discutere";

		$text = $this->instance->get_subtitle( "DxHd4_i_tS0", 'transcript_post_type' );
		

		$this->assertEquals( 
            $expected_text, 
            $text, 
            "actual value is not equals to expected"
        );
	 }

}
