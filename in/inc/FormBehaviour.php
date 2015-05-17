<?php
class FormBehaviour {
	// Variável que armazena a conexão
	private $conn 					= null;
	// Variável que armazena as questões sorteadas
	private $prod					= null;
	private $maxQuestionsPerPage 	= 10;
	//variveis necessarias para mostrar resultado
	private $correctQ				= null;
	private $wrongQ					= null;
	// Variaáveis de armazenamento pós-correção
	private $correctAnswers			= new Array();
	private $incorrectAnswers 		= new Array();
	private $incorrectAnswersHTML 	= null;
	private $correctionString 		= null;
	// Esta função é iniciada junto com a classe
	public function __construct() {}

	// Realiza a conexão com o Banco de Dados
	private function databaseConnection() {
		if ($this->conn != null) {
            return true;
        } else {
            try {
                $this->conn = new PDO('mysql:host=127.0.0.1;dbname=u395938104_1;charset=utf8', 'root', '');//depois de termina taca new PDO('mysql:host=mysql.hostinger.com.br;dbname=u395938104_1;charset=utf8', 'u395938104_adm', 'admpass');
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return true;
            } catch (PDOException $e) {
                die ('ERROR_DB_CONN');
                return false;
            }
        }
	}

	// Corrige os exercícios recebidos
	private function correction($questao, $resposta) {
		$q = explode(",", $questao);
		$r = explode(",", $resposta);
		if($this->databaseConnection()) {
			$stmt = $this->conn->prepare('SELECT res FROM questions WHERE id IN ('.implode(", ", $q).');');
			$stmt->execute();
			$rows = $stmt->fetchAll();
			$z=count($r)-1;
			$row=$this->fixSequence($rows,$q);
			for($i=0; $i<=$z; $i++) {
				if(empty($r[$i])) {
					$fqX[$i] = $q[$i]."X";
					echo $fqX[$i]."<br/>";
				} else {
					if ($row[$i] == $r[$i]) {
						$fqC[$i] = $q[$i]."C";
						echo $fqC[$i]."<br/>";
					} elseif ($row[$i] != $r[$i]) {
						$fqE[$i] = $q[$i]."E";
						echo $fqE[$i]."<br/>";
					} else {
						echo "erro na correcao<br/>";
					}
				}
			}
			if (!empty($fqX)) {	$Xcorecao = implode(";", $fqX); } else {$Xcorecao="";}
			if (!empty($fqC)) {	$Ccorecao = implode(";", $fqC); } else {$Ccorecao="";}
			if (!empty($fqE)) {	$Ecorecao = implode(";", $fqE); } else {$Ecorecao="";}
			$Result = $Ecorecao."_".$Ccorecao."_".$Xcorecao;
			return $Result;
		}
	}

	// Mantem a sequencia dos exercícios sorteados
	private function fixSequence($ro, $ques) {
		$sq = $ques;
		sort($sq);
		$z=count($ques);
		$p=count($ro);
		for($i=0;$i<$z;$i++) {
			for($i2=0;$i2<$z;$i2++) {
				if($ques[$i2] == $sq[$i]){ $qs[$i2]=$ro[$i]['res'];}
			}
		}
		return $qs;
	}

	//Realiza o processo de mostragem e correção simulktânea
	public function setData() {
		if(isset($_POST['questions_form_submit']) && isset($_SESSION['prod'])) {
			$questions = "";
			$answers = "";
			$a = 0;
			foreach ($_SESSION['prod'] as $z) {
				if($a == 0) {
					$questions .= $z[0];
				} else {
					$questions .= ",".$z[0];
				} $a++;
			} $a = 0;
			foreach ($_POST as $i) {
				if($a == 0){
					$answers.="".$i;
				} else {
					$answers.=",".$i;
				} $a++;
			}
			$dataString = $this->correction($questions, $answers);
			// ...
			$this->setIncorrectAnswersHTML();
			unset($_SESSION['prod']);
		} else {
			echo "ERRO #SETDATA1";
		}
	}

	// Adiciona as questões ao HTML
	public function printQuestions($prod) {
		$x = count($prod);
		$output = '	<div class="simple-container">
						<div class="content">
							<h1 id="curr_page" style="color: #003A91;">Página 0 de 0.</h1>
							<form action="" method="post" name="FormQuestions">';
		for ($i=0; $i < $x; $i++) {
			$a = $prod[$i];
			$l = "div".$i;
			$output .= '<div class="q-box" id="div'.$i.'">
							<div class="q-top-box">
								<div class="top">
									<p> '.$a[1].' - '.$a[2].' ('.$a[3].')</p><hr>
									<p>'.$a[4].'</p>
								</div>
							</div>
							<div class="q-alt-box">
								<div class="alt">
									<input value="1" type="radio" id="'.$a[0].'1" name="id_res'.$i.'" onclick="feito(\''.$l.'\');"></input><label for="'.$a[0].'1">'.$a[5].'</label>
									<input value="2" type="radio" id="'.$a[0].'2" name="id_res'.$i.'" onclick="feito(\''.$l.'\');"></input><label for="'.$a[0].'2">'.$a[6].'</label>
									<input value="3" type="radio" id="'.$a[0].'3" name="id_res'.$i.'" onclick="feito(\''.$l.'\');"></input><label for="'.$a[0].'3">'.$a[7].'</label>
									<input value="4" type="radio" id="'.$a[0].'4" name="id_res'.$i.'" onclick="feito(\''.$l.'\');"></input><label for="'.$a[0].'4">'.$a[8].'</label>
									<input value="5" type="radio" id="'.$a[0].'5" name="id_res'.$i.'" onclick="feito(\''.$l.'\');"></input><label for="'.$a[0].'5">'.$a[9].'</label>
								</div>
							</div>';
			if(!empty($a[12])) {
				$output .= '<div class="q-top-box">
								<div class="top">
									<p>'.$a[12].'</p>
								</div>
							</div>
						</div>';
			} else {
				$output .= '</div>';
			}
		}
		$output .= '<input type="button" name="questions_form_submit" onclick="btFaz();" class="btn btn-lg btn-default" value="Próxima">
				</form>
			</div>
		</div>';
		echo $output;
	}

	// Mostra o resultado do formulário
	public function printResult() {
		$htmlString = '
			<div class="simple-container">
				<div class="content">
					<h1 style="color: #003A91;">Resultado</h1>
					<div class="q-box">
						<div class="q-top-box">
							<div class="top">
								<p>Pontuação:<h2 style="float: right;">'. (count($this->correctAnswers)*100)/count($_SESSION['prod']) .'%</h2></p>
							</div>
						</div>
						<div class="q-alt-box">
							<div class="alt">
								<p>Acertos: <h2 style="float: right;">'.count($this->correctAnswers).'</h2></p>
								<p>Erros: : <h2 style="float: right;">'.count($this->incorrectAnswers).'</h2></p>
							</div>
						</div>
						<div class="q-top-box">
							<div class="top">
								<a style="float: right;" href="javascript:{}" onclick="showIncorrectQuestions();">Clique aqui para visualizar as questões incorretas!</a>
							</div>
						</div>
					</div>
					<div class="incorrectAnswersWrapper">'.$this->incorrectAnswersHTML.'</div>
				</div>
			</div>
		';
	}
}
?>