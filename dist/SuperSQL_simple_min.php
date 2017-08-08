<?php
/*
 Author: Andrews54757
 License: MIT (https://github.com/ThreeLetters/SuperSQL/blob/master/LICENSE)
 Source: https://github.com/ThreeLetters/SQL-Library
 Build: v2.5.0
 Built on: 08/08/2017
*/

// lib/connector/index.php
class Response{public$result;public$affected;public$ind;public$error;public$errorData;function __construct($a,$b){$this->error=!$b;if(!$b){$this->errorData=$a->errorInfo();}else{$this->result=$a->fetchAll();$this->affected=$a->rowCount();}$this->ind=0;$a->closeCursor();}function error(){return$this->error ?$this->errorData : false;}function getData(){return$this->result;}function getAffected(){return$this->affected;}function next(){return$this->result[$this->ind++];}function reset(){$this->ind=0;}}class Connector{public$queries=array();public$db;public$log=array();public$dev=false;function __construct($c,$d,$e){$this->db=new \PDO($c,$d,$e);$this->log=array();}function query($f,$g=null){$h=$this->db->prepare($f);if($g)$i=$h->execute($g);else$i=$h->execute();if($this->dev)array_push($this->log,array($f,$g));return new Response($h,$i);}function _query($j,$k,$l,$m){if(isset($this->queries[$j."|".$m])){$n=$this->queries[$j."|".$m];$h=$n[1];$o=&$n[0];foreach($k as$p=>$q){$o[$p][0]=$q[0];}if($this->dev)array_push($this->log,array("fromcache",$j,$m,$k,$l));}else{$h=$this->db->prepare($j);$o=$k;foreach($o as$p=>&$r){$h->bindParam($p + 1,$r[0],$r[1]);}$this->queries[$j."|".$m]=array(&$o,$h);if($this->dev)array_push($this->log,array($j,$m,$k,$l));}if(count($l)==0){$i=$h->execute();return new Response($h,$i);}else{$s=array();$i=$h->execute();array_push($s,new Response($h,$i));foreach($l as$p=>$t){foreach($t as$u=>$v){$o[$u][0]=$v;}$i=$h->execute();array_push($s,new Response($h,$i));}return$s;}}function close(){$this->db=null;$this->queries=null;}function clearCache(){$this->queries=array();}}
// lib/parser/Simple.php
class SimParser{public static function escape($a){$b=strtolower(gettype($a));if($b=="boolean"){$a=$a ? "1" : "0";}else if($b=="string"){$a="'".$a."'";}else if($b=="double"){$a=(int)$a;}else if($b=="null"){$a="0";}return$a;}public static function WHERE($c,&$d){if(count($c)!=0){$d.=" WHERE ";$e=0;foreach($c as$f=>$a){if($e!=0){$d.=" AND ";}$d.="`".$f."` = ".$self::escape($a);$e++;}}}public static function SELECT($g,$h,$c,$i){$d="SELECT ";$j=count($h);if($j==0){$d.="*";}else{for($e=0;$e<$j;$e++){if($e!=0){$d.=", ";}$d.="`".$h[$e]."`";}}$d.="FROM `".$g."`";self::WHERE($c,$d);$d.=" ".$i;return$d;}public static function INSERT($g,$k){$d="INSERT INTO `".$g."` (";$l=") VALUES (";$e=0;foreach($k as$f=>$a){if($e!=0){$d.=", ";$l.=", ";}$d.="`".$f."`";$l.=self::escape($a);$e++;}$d.=$l;return$d;}public static function UPDATE($g,$k,$c){$d="UPDATE `".$g."` SET ";$e=0;foreach($k as$f=>$a){if($e!=0){$d.=", ";}$d.="`".$f."` = ".self::escape($a);$e++;}self::WHERE($c,$d);return$d;}public static function DELETE($g,$c){$d="DELETE FROM `".$g."`";self::WHERE($c,$d);return$d;}}
// index.php
class SuperSQL{public$con;function __construct($a,$b,$c){$this->con=new Connector($a,$b,$c);}function sSELECT($d,$e=array(),$f=array(),$g=""){$h=SimParser::SELECT($d,$e,$f,$g);return$this->con->query($h);}function sINSERT($d,$i){$h=SimParser::INSERT($d,$i);return$this->con->query($h);}function sUPDATE($d,$i,$f=array()){$h=SimParser::UPDATE($d,$i,$f);return$this->con->query($h);}function sDELETE($d,$f=array()){$h=SimParser::DELETE($d,$f);return$this->con->query($h);}function query($j,$k=null){return$this->con->query($j,$k);}function close(){$this->con->close();}function dev(){$this->con->dev=true;}function getLog(){return$this->con->log;}function clearCache(){$this->con->clearCache();}function transact($l){$this->con->db->beginTransaction();$m=$l($n);if($m===false)$n->con->db->rollBack();else$n->con->db->commit();return$m;}}
?>