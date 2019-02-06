<?php

	/*********************
	# Classe permettant d'effectuer des requêtes préparées et de les logguer (ex:dbFactory)
	*
	*	@author : Gaultier D'ACUNTO
	*	@date : 25/11/2018
	*	@version : 1.0.0
	*
	*/
	class Requester{


		/**********
		# Récupération des données de connexion aux BDD
		*/
		public function __construct(){

			$this->state = 'PSQL'; // Librairie utilisée (PDO ou PostgreSQL)
			$this->type = 'assoc'; // Type de retour désiré (par défaut : associatif)

		}


		/**********
		# Récupération du dernier id inséré
		*
		*	@return $id : int : dernier ID inséré en base
		*
		*/
		public function lastId(){

			$id = $this->stmt->lastInsertId();
			return $id;

		}


		/**********
		# Permet la connexion à une nouvelle DB, en utilisant PostgreSQL ou MySQL
		*
		* 	@param $engine : varchar - type de bdd (PgSQL / MySQL)
		* 	@apram $db : varchar - nom de la base de données à interroger
		*
		*/
		public function setDB($engine,$db){

			$eng = strtolower($engine);
			if($eng == 'psql' || $engine == 'postgresql'){

				$this->stmt = new PDO("pgsql:dbname=".$db.";host=".HOST, USER, PSWD);
				$this->state = 'PSQL';

			}else{

				$this->stmt = new PDO('mysql:host='.$this->ipFR.';dbname='.$db, $this->userFR, $this->passFR);
				$this->state = 'PDO';
				
			}

		}


		/**********
		# Fixe le type de retour désiré, Objet ou tableau associatif
		*
		*	@param $targ : varchar - obj (Objet), assoc (Associatif)
		*
		*/
		public function returnType($targ){

			if($targ == 'obj'){
				$this->type = 'obj';
			}else{
				$this->type = 'assoc';
			}

		}


		/**********
		# Permet de compter le nombre de lignes de la dernière requête
		*
		*	@return $this->counter : int - nombre de résultats trouvés pour la dernière requête envoyée
		*
		*/
		public function xCount(){

			return $this->counter;

		}


		/**********
		# Permet d'effectuer une requête préparée sous n'importe quelle forme
		*
		*	@param $query : varchar - requête complète
		*
		*/
		public function xQuery($query){

			$val = array();
			$i = 0;
			$cp = 0; // Checkpoint
			$q = ''; // Query
			$p = ''; // Clear Query
			$next = false;
			$checker = array('=','>','<','LIKE','NOT LIKE');
			$toR = array();

			$tmp = explode("\n",$query);
			foreach($tmp as $row){
				$dab = explode(' ',$row);
				$val[$i] = $dab;
				$i++;
			}

			$rgw = array();
			foreach($val as $tot){
				foreach($tot as $row){
					$row = preg_replace('/\s+/','',$row);
					if($row != '' && $tot != ''){
						array_push($rgw,$row);
					}
				}
			}

			$i = 0;
			if($rgw[0] != 'INSERT'){
				foreach($rgw as $dev){
					if(preg_match('/JOIN/i',$dev)){
						$next = true;
					}
					if($cp == true){
						if($next != true){
							if(is_numeric($dev)){
								if(preg_match('/\./',$dev)){
									$dev = floatval($dev);
								}else{
									$dev = intval($dev);
								}
							}
							$q .= ':'.$i.' ';
							$toR[$i] = $dev;
							$p .= $dev.' ';
							$cp = false;
							$i++;
						}else{
							$q .= $dev.' ';
							$p .= $dev.' ';
							$next = false;
							$cp = false;
						}
					}else{
						foreach($checker as $row){
							if(preg_match('/'.$row.'/i',$dev)){
								$cp = true;
							}
						}
						$q .= $dev.' ';
						$p .= $dev.' ';
					}
				}

			}else{

				$v = 0;
				$next = false;
				$flag = false;
				foreach($rgw as $dev){

					if(preg_match('/VALUES/i',$dev)){
						$next = true;
					}else{
						if($next !== true){
							$next = false;
						}else{
							$dev = str_replace(array('(',')'),'',$dev);
							$exp = explode(',',$dev);
							$counter = count($exp);
							foreach($exp as $row){
								$br = '';
								if($flag === true){
									$j = $i+2;
								}else{
									$j = $i+1;
								}
								if($row != 'DEFAULT'){
									if($j != $counter){
										$br = ',';
									}
									$q .= ':'.$i.$br;
									if(is_numeric($row)){
										if(preg_match('/\./',$row)){
											$dev = floatval($row);
										}else{
											$dev = intval($row);
										}
									}
									$toR[$i] = $row;
									$i++;
								}else{
									if($j != $counter){
										$br = ',';
									}
									$q .= 'DEFAULT'.$br;
									$flag = true;
								}
							}
							$rgw[$v] = '('.$q.')';
						}
					}
					$v++;
				}

				$q = implode(' ',$rgw);
	
			}

			$query = $this->stmt->prepare($q);
			for($j=0;$j<$i;$j++){
				if(is_int($toR[$j]) || is_float($toR[$j])){
					$query->bindParam(':'.$j,$toR[$j],PDO::PARAM_INT);
				}else{
					$query->bindParam(':'.$j,$toR[$j],PDO::PARAM_STR);
				}
			}
			
			$verif = $query->execute();
			$this->counter = $query->rowCount();
			if($this->type == 'obj'){
				$query->setFetchMode(PDO::FETCH_OBJ);
			}else{
				$query->setFetchMode(PDO::FETCH_ASSOC);
			}

			$this->result = $query->fetchAll();
			$query->closeCursor();			

			return $this->result;

		}



	}



	
