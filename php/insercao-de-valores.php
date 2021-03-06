<?php
require_once("custom/php/common.php");

/**
 * Class that handle all the methods that are necessary to execute this component
 */
class InsertValues{
    private $db;            // Object from DB_Op that contains the access to the database
    private $capability;    // Wordpress's Capability for this component

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "insert_values";
        $this->executaScript();
    }

    /**
     * Main method that controls the capability of the current user to acces this component
     */
    public function executaScript()
    {
        // Check if any user is logged in
        if ( is_user_logged_in() )
        {
            // Check if the current user as the capability to use this component
            if(current_user_can($this->capability))
            {
                $this->verificaEstado();
            }
            else
            {
        ?>
            <html>
                <p>Não tem autorização para aceder a esta página</p>
            </html>
        <?php
            }
        }
        else
        {
        ?>
            <html>
                <p>Não tem sessão iniciada.</p>
                <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
            </html>
        <?php
        }
    }

    /**
     * Method that controls the execution flow of this component
     */
    private function verificaEstado()
    {
        if (empty($_REQUEST["estado"]))
        {
            $this->estadoEmpty();
        }
        elseif ($_REQUEST["estado"] === "introducao")
        {
            $this->estadoIntroducao();
            
        }
        elseif($_REQUEST['estado'] =='validar')
        {
            $this->estadoValidar();
        }
        elseif($_REQUEST['estado'] =='inserir')
        {
            $this->estadoInserir();
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is empty
     */
    private function estadoEmpty() {
?>
        <h3>Inserção de valores - escolher entidade/formulário customizado</h3>
<?php
        $entidades = $this->db->runQuery("SELECT * FROM ent_type ORDER BY name ASC");
        $row_cnt = $entidades->num_rows;
        //check if there are any entities in the DB
        if($row_cnt == 0)
        {
?>
            <p>Não pode inserir valores uma vez que ainda não foram introduzidos tipos de entidades.</p>
            <p>Clique em <a href="/gestao-de-entidades">Gestão de entidades</a> para adicionar um novo tipo de entidade</p>
<?php
        }
        else
	{
?>
            <!--create a list with all the entities-->
            <ul>
            <li>Entidade:
            <ul>
<?php            
            
            // get all the entities to list                    
            $queryEntity = "SELECT * FROM `ent_type`";

            $executaEntity = $this->db->runQuery($queryEntity);
            // guarda um array associativo que recebe a informação da query, 
            while($arrayEntity = $executaEntity->fetch_assoc())
            {
                    //ligação de cada item ao endereço Inserção de Valores
                    echo'<li><a href="insercao-de-valores?estado=introducao&ent='.$arrayEntity['id'].'">['.$arrayEntity['name'].']</a>';
            }
?>
            
            </ul>
             <!--create a list with all the entities-->
            </li>
            <li>Formulários customizados:
            <ul>
<?php            
            
            // get all the entities to list                    
            $queryCustForm = "SELECT * FROM `custom_form`";

            $executaCustForm = $this->db->runQuery($queryCustForm);
            // guarda um array associativo que recebe a informação da query, 
            while($arrayCustForm= $executaCustForm->fetch_assoc())
            {
                    //ligação de cada item ao endereço Inserção de Valores
                    echo'<li><a href="insercao-de-valores?estado=introducao&form='.$arrayCustForm['id'].'">['.$arrayCustForm['name'].']</a>';
            }
?>   
            </ul>
            </li>
            </ul>
            
<?php
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "introducao".
     * This method creates the dynamic form created from the proiperties associated to the entiity or form selected before
     */
    private function estadoIntroducao() {
        if (!empty($_REQUEST["ent"]))
        {
            $_SESSION["tipo"] = "ent";
        }
        else {
            $_SESSION["tipo"] = "form";
        }
        
        $tipo = $_SESSION["tipo"];
        
        $_SESSION[$tipo."_id"] = $_REQUEST[$tipo];
        
        // need to get the name form the entity or from the form according with the selection form the last state
        if ($tipo === "ent"){
            $queryNome = "SELECT name FROM ent_type WHERE id = ".$_SESSION[$tipo."_id"];
        }
        else{
            $queryNome = "SELECT name FROM custom_form WHERE id = ".$_SESSION[$tipo."_id"];
        }
        $name = $this->db->runQuery($queryNome);
        $_SESSION[$tipo."_name"] = $name->fetch_assoc()["name"];
?>
        <h3>Inserção de valores - <?php echo $_SESSION[$tipo."_name"];?></h3>
        <form method="POST" name="<?php echo $tipo."_".$_SESSION[$tipo."_id"];?>" action="?estado=validar&<?php echo $tipo;?>=<?php echo $_SESSION[$tipo."_id"];?>">
            <p class="mandatory">Os campos marcados com * são de preenchimento obrigatorio</p>
<?php
       if ($tipo === "ent"){
           $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_SESSION[$tipo."_id"]." AND state = 'active' ORDER BY form_field_order ASC";
       }
       else {
           $queryProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$_SESSION[$tipo."_id"]." AND prop.id = cfhp.property_id AND prop.state = 'active' ORDER BY cfhp.field_order ASC";
       }
       $execQueryProp = $this->db->runQuery($queryProp);
       $arrayEntidades = array(); //array that will store all the entities that ar involved in the custom_form
       while ($arrayProp = $execQueryProp->fetch_assoc())
       {
           $un = $this->obtemUnidades($arrayProp["unit_type_id"]);
           
           if ($tipo === "ent") {
?>
            <label><?php echo $arrayProp["name"];?>
<?php
                if ($arrayProp["mandatory"] == 1) {
?>
                    <span class="mandatory">*</span></label><br>
<?php 
                }
                else {
?>
                     </label><br>
<?php 
                }
           }
           else {
               $getEntidade = "SELECT * FROM ent_type WHERE id = ".$arrayProp["ent_type_id"];
               $entidade = $this->db->runQuery($getEntidade)->fetch_assoc();
               $arrayEntidades[$entidade["id"]]=$entidade["name"];
?>
            <label><?php echo $entidade["name"]."-".$arrayProp["name"];?>
<?php
                if ($arrayProp["mandatory_form"] == 1) {
?>
                    <span class="mandatory">*</span></label><br>
<?php 
                }
                else {
?>
                     </label><br>
<?php 
                }
           }
            switch ($arrayProp["value_type"])
            {
                case "text":
                    if ($arrayProp["form_field_type"] === "text")
                    {
?>
                        <input type="text" name="<?php echo $arrayProp["form_field_name"];?>"> <?php echo $un["name"];?><br><br>
<?php
                    }
                    else if ($arrayProp["form_field_type"] === "textbox")
                    {
                        $colunas = substr($arrayProp["form_field_size"], 0, 2);
                        $linhas = substr($arrayProp["form_field_size"], 3, 2);;
?>
                        <textarea  name="<?php echo $arrayProp["form_field_name"];?>" rows="<?php echo $linhas;?>" cols="<?php echo $colunas;?>"></textarea><?php echo $un["name"];?><br><br>
<?php
                    }                    
                    break;
                case "bool":
?>
                    <input type="radio" name="<?php echo $arrayProp["form_field_name"];?>" value="true">Sim<br>
                    <input type="radio" name="<?php echo $arrayProp["form_field_name"];?>" value="false">Não<br><br>
<?php                    
                    break;
                case "int":
                case "double":
?>
                    <input type="text" name="<?php echo $arrayProp["form_field_name"];?>"> <?php echo $un["name"];?><br><br>
<?php
                    break;
                case "enum":
                    $querySelVal = "SELECT * FROM prop_allowed_value WHERE state = 'active' AND property_id = ".$arrayProp["id"];
                    $relSelVal = $this->db->runQuery($querySelVal);
                    if ($arrayProp["form_field_type"] === "selectbox")
                    {
?>
                        <select name="<?php echo $arrayProp["form_field_name"];?>">
                            <option></option>
<?php
                    }
                    while ($allowVal = $relSelVal->fetch_assoc())
                    {
                        if ($arrayProp["form_field_type"] === "radio")
                        {
?>
                            <input type="radio" name="<?php echo $arrayProp["form_field_name"];?>" value="<?php echo $allowVal["value"];?>"><?php echo $allowVal["value"];?> <?php echo $un["name"];?><br>
<?php
                        }
                        else if ($arrayProp["form_field_type"] === "checkbox")
                        {
?>
                            <input type="checkbox" name="<?php echo $arrayProp["form_field_name"];?>" value="<?php echo $allowVal["value"];?>"><?php echo $allowVal["value"];?> <?php echo $un["name"];?><br>
<?php
                        }
                        else if ($arrayProp["form_field_type"] === "selectbox")
                        {
?>
                            <option value="<?php echo $allowVal["value"];?>"><?php echo $allowVal["value"];?></option> <?php echo $un["name"];?>
<?php
                        }
                    }
                    if ($arrayProp["form_field_type"] === "selectbox")
                    {
?>
                        </select>
<?php
                    }
?>
                    <br><br>
<?php
                    break;
                case "ent_ref":
 ?>
                    <select name="<?php echo $arrayProp['form_field_name'];?>">
                        <option></option>
<?php
                    if ($tipo === "form") {
                        $idEntidades = $this->idEntRel($_SESSION[$tipo."_id"])[0];
                    }
                    else {
                        $idEntidades[$_SESSION[$tipo."_id"]] = $_SESSION[$tipo."_id"];
                    }
                    foreach ($idEntidades as $id => $idEnt) {
                        //vai buscar todos as referencias a entidades que tem como chave estrangeira uma referenca a outra entidade
                        $selecionaFK = $this->db->runQuery("SELECT `fk_ent_type_id` FROM `property` WHERE ".$id." = ent_type_id AND value_type = 'ent_ref'");
                        while($FK = $selecionaFK->fetch_assoc())
                        {
                            
                            $nomeEntRef = $this->db->runQuery("SELECT name FROM ent_type WHERE ".$FK['fk_ent_type_id']." = id")->fetch_assoc()["name"];
                            // vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
                            $selecionainstancia = $this->db->runQuery("SELECT `id`, `entity_name` FROM `entity` WHERE ent_type_id = ".$FK['fk_ent_type_id']."");
                            if ($this->verificaEntRef($FK['fk_ent_type_id'], $idEntidades)) {
?>
                                <option value="instPorCriar"><?php echo $nomeEntRef;?> que está a criar</option>
<?php
                            }
                            //array associativo que guarda o resultado que vem da query 
                            while($nomeinstancia = $selecionainstancia->fetch_assoc())
                            {
                                //criação das opções dinamicas que recebm o nome do componente que vem do array associativo
                                if (empty($nomeinstancia['entity_name'])) {
?>
                                    <option value="<?php echo $nomeinstancia['id'];?>"><?php echo $nomeinstancia['id'];?></option>
<?php
                                }
                                else {
?>
                                    <option value="<?php echo $nomeinstancia['id'];?>"><?php echo $nomeinstancia['entity_name'];?></option>
<?php
                                }
                            }
                        }
                    }
?>
                    </select></br>
<?php
                    break;
                default :
                    break;
            }
        }
        if ($tipo === "ent") {
?>
            <label>Nome para instância da entidade</label><br>
            <input type="text" name="nomeInst"><br><br>
<?php
        }
        else {
            foreach ($arrayEntidades as $id => $nome) {
?>
            <label>Nome para instância da entidade <?php echo $nome; ?></label><br>
            <input type="text" name="nomeInst_<?php echo $id; ?>"><br><br>
<?php
        }
    }
?>
            <input hidden="hidden" name="estado" value="validar">
            <input type="submit" value="Submeter">           
        </form>
<?php  
    }
    
    /**
     * This method checks if the referenced entity is on the form presented
     */
    private function verificaEntRef ($idEntRef, $arrayEnt) {
        $pertence = false;
        foreach ($arrayEnt as $key => $value) {
            if ($idEntRef == $key && $pertence == false) {
                $pertence = true;
            }
        }
        return $pertence;
    }    

    /**
     * This method is responsible to control the flow execution when state is "inserir"
     */
    private function estadoInserir() {
        $tipo = $_SESSION["tipo"];
?>
        <h3>Inserção de valores - <?php echo $_SESSION[$tipo."_name"] ?> - inserção </h3>
<?php						
        //creation of transaction because we will insert values in more than one tables
        $this->db->getMysqli()->autocommit(false);
        $this->db->getMysqli()->begin_transaction();
        if ($tipo === "form")
        {
            $arrayEntRel = $this->idEntRel($_SESSION[$tipo."_id"]);
            $arrayEnt = $arrayEntRel[0];
            $arrayRel = $arrayEntRel[1];
            $i = 0;
            foreach ($arrayEnt as $id=>$ent) {
                    $controlo[$i] = $this->insertEntityValues($id);
            }
            $sucesso = true;
            foreach ($controlo as $value) {
                if ($sucesso === true && $value === false)
                {
                    $sucesso = false;
                }
            }
            /*foreach ($arrayRel as $rel) {
                $this->insertRelValues($rel);
            }*/
        }
        else {
            $sucesso = $this->insertEntityValues($_SESSION[$tipo."_id"]);
        }
        if($sucesso == true)
        {
?>
            <p>Inseriu o(s) valor(es) com sucesso.</p></br>
            <p>Clique em <a href="/insercao-de-valores">Voltar</a> para voltar ao início da inserção de valores e poder escolher outro componente ou em <a href="?estado=introducao&<?php echo $tipo;?>=<?php echo $_SESSION[$tipo."_id"];?>">Continuar a inserir valores nesta(s) entidade(s)</a> se quiser continuar a inserir valores.</p>
<?php
            if ($tipo === "ent") {
                $querySelUlt = "SELECT * FROM entity WHERE ent_type_id = ".$_SESSION[$tipo."_id"]." ORDER BY id DESC LIMIT 1";
                $ult = $this->db->runQuery($querySelUlt)->fetch_assoc();
?>
            <p>Clique em <a href="/insercao-de-relacoes?estado=associar&ent=<?php echo $ult["id"];?>">Associar entidades</a>, caso deseje associar a entidade <?php echo $ult["entity_name"];?>, com uma outra já previamente criada.</p>
<?php
            } else {
                foreach ($arrayEnt as $id=>$ent) {
                    $querySelUlt = "SELECT * FROM entity WHERE ent_type_id = ".$id." ORDER BY id DESC LIMIT 1";
                    $ult = $this->db->runQuery($querySelUlt)->fetch_assoc();
?>
                    <p>Clique em <a href="/insercao-de-relacoes?estado=associar&ent=<?php echo $ult["id"];?>">Associar entidades</a>, caso deseje associar a entidade <?php echo $ult["entity_name"];?>, com uma outra já previamente criada.</p>
<?php
                }
            }
        }

        else
        {
?>
            <p>Lamentamos, mas ocorreu um erro.</p>
<?php
            goBack();
        }
    }

    
    /**
     * Method that inserts the new instance of the entity
     * @param type $idEnt (the id of the entity that we will create a new instance)
     * @return boolean
     */
    private function insertEntityValues($idEnt) {
        $tipo = $_SESSION["tipo"];
        if ($tipo === "ent") {
            $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`, `entity_name`, `updated_on`) VALUES (NULL,".$idEnt.", '".$_REQUEST["nomeInst"]."','".date("Y-m-d H:i:s",time())."')";
        }
        else {
            $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`, `entity_name`, `updated_on`) VALUES (NULL,".$idEnt.", '".$_REQUEST["nomeInst_".$idEnt]."','".date("Y-m-d H:i:s",time())."')";
        }
        
        $resInsertInst = $this->db->runQuery($queryInsertInst);
        if(!$resInsertInst) {
            $this->db->getMysqli()->rollback();
?>
            <p>Erro na criação da instância.</p>
<?php				
        }
        else {
            $idEntForm = $this->db->getMysqli()->insert_id;
            $propriedadesEnt = $this->db->runQuery("SELECT * FROM `property` WHERE state = 'active' AND ent_type_id = ".$idEnt);
            if(!$propriedadesEnt) {
                $this->db->getMysqli()->rollback();
?>
                <p>Erro na selação da propriedade.</p>
<?php
            }
            else {

                $sucesso = false;
                if ($propriedadesEnt->num_rows == 0) {
                    $this->db->getMysqli()->commit();
                    $sucesso = true;
                }
                else {
                    while($propriedades = $propriedadesEnt->fetch_assoc())
                    {
                        if (!empty($_REQUEST[$propriedades['form_field_name']])) {
                            if ($_REQUEST[$propriedades['form_field_name']] === "instPorCriar") {
                                $querySelFK = "SELECT `fk_ent_type_id` FROM `property` WHERE ".$idEnt." = ent_type_id AND value_type = 'ent_ref'";
                                //echo $querySelFK;
                                $fk = $this->db->runQuery($querySelFK)->fetch_assoc()["fk_ent_type_id"];
                                $querySelUltRef = "SELECT * FROM entity WHERE ent_type_id = ".$fk." ORDER BY id DESC LIMIT 1";
                                //echo $querySelUltRef;
                                $selUltRef = $this->db->runQuery($querySelUltRef);
                                $ultRef = $selUltRef->fetch_assoc();
                                $_REQUEST[$propriedades['form_field_name']] = $ultRef["id"];
                            }
                            $insertVal = $this->db->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `updated_on`) "
                                . "VALUES (NULL,".$idEntForm.",".$propriedades['id'].",'".$_REQUEST[$propriedades['form_field_name']]."','".wp_get_current_user()->user_login."','".date("Y-m-d H:i:s",time())."')");

                            if(!$insertVal)
                            {								
                                $this->db->getMysqli()->rollback();
?>
                                <p>Erro na atribuição do valor à propriedade <?php echo $propriedades["name"];?>.</p>
<?php
                                $sucesso = false;
                                break;
                            }
                            else
                            {
                                $this->db->getMysqli()->commit();
                                $sucesso = true;
                            }	
                        }

                    }
                }
            }
        }
        return $sucesso;
    }
    
    /**
     * This method is responsible to control the flow execution when state is "validar"
     */
    private function estadoValidar() {
        $tipo = $_SESSION["tipo"];
?>
        <h3>Inserção de valores - <?php echo $_SESSION[$tipo."_name"];?> - validar</h3>
<?php
        if ($tipo === "ent"){
           $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_SESSION[$tipo."_id"]." AND state = 'active' ORDER BY form_field_order ASC";
       }
       else {
           $queryProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$_SESSION[$tipo."_id"]." AND prop.id = cfhp.property_id AND prop.state = 'active' ORDER BY cfhp.field_order ASC";
       }
       $execQueryProp = $this->db->runQuery($queryProp);
       $goBack = false;
       while ($arrayProp = $execQueryProp->fetch_assoc()) {
           if ($tipo == "ent") {
               if ($arrayProp["mandatory"] == 1  && empty($_REQUEST[$arrayProp["form_field_name"]])){
?>
                    <p>O campo <?php echo $arrayProp["name"];?> é de preenchimento obrigatório!</p>
<?php
                    goBack();
                    $goBack = true;
                    break;
               }           
           }
           else {
               if ($arrayProp["mandatory_form"] == 1  && empty($_REQUEST[$arrayProp["form_field_name"]])){
?>
                    <p>O campo <?php echo $arrayProp["name"];?> é de preenchimento obrigatório!</p>
<?php
                    goBack();
                    $goBack = true;
                    break;
               }  
           }
           if (($arrayProp["form_field_type"] == 'text' || $arrayProp["form_field_type"] == 'textbox') && (strlen($_REQUEST[$arrayProp["form_field_name"]]) > $arrayProp["form_field_size"])) {
?>
                <p>O valor introduzido no campo <?php echo $arrayProp['name'];?> tem muitos carateres.</p>
<?php
                goBack();
                $goBack = true;
                break;
           }
           else {
               if (empty ($_REQUEST[$arrayProp["form_field_name"]])) {
                   $propVal = NULL;
               }
               else {
                   $propVal = $this->db->getMysqli()->real_escape_string($_REQUEST[$arrayProp["form_field_name"]]);
               }
               switch ($arrayProp["value_type"]) {
                   case "int":
                       if (!is_null($propVal)) {
                           if(ctype_digit($propVal))
                            {
                                $propVal = (int)$propVal;
                                //quando o request tem um int e trata o int,actualiza esse valor com esse valor tratado
                                $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                            }
                            else
                            {
?>
                                <p>Certifique-se que introduziu um número inteiro no campo <?php echo $arrayProp['name'];?>.</p>
<?php
                                goBack();
                                $goBack = true;
                            }
                        }
                        else {
                            $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                        }
                       break;
                   case "double":
                        if (!is_null($propVal)) {
                            if(is_numeric($propVal))
                            {
                                $propVal = floatval($propVal);
                                //quando o request tem um double e trata o double,actualiza esse valor com esse valor tratado
                                $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                            }
                            else
                            {
?>
                                <p>Certifique-se que introduziu um valor numérico no campo <?php echo $arrayProp['name'];?>.</p>
<?php
                                goBack();
                                $goBack = true;
                            }
                        }
                        else {
                            $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                        }
                       break;
                   default:
                        $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                       break;
               }
               if ($goBack) {
                   break;
               }
           }
           if ($tipo === "form") {
               $getEntidade = "SELECT * FROM ent_type WHERE id = ".$arrayProp["ent_type_id"];
               $entidade = $this->db->runQuery($getEntidade)->fetch_assoc();
               $arrayEntidades[$entidade["id"]] = $entidade["name"];  
               $_REQUEST["nomeInst_".$entidade["id"]] = $this->db->getMysqli()->real_escape_string($_REQUEST["nomeInst_".$entidade["id"]]);
           }
           else {
                $_REQUEST["nomeInst"] = $this->db->getMysqli()->real_escape_string($_REQUEST["nomeInst"]);
           }
       }
       
       if (!$goBack) {
?>
            <form method="POST" action="?estado=inserir&<?php echo $tipo;?>=<?php echo $_SESSION[$tipo."_id"]?>">
                <p>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p>
                <ul>
                    <li><?php echo $_SESSION[$tipo."_name"];?>
                    <ul>
<?php
            $execQueryProp = $this->db->runQuery($queryProp);
            while ($arrayProp = $execQueryProp->fetch_assoc()) {
                if (is_null($_REQUEST[$arrayProp['form_field_name']])){
                    $valor = "Não introduziu nenhum valor";
                }
                else if ($_REQUEST[$arrayProp['form_field_name']] == "instPorCriar") {
                    $valor = "Nome da instância referenciada que também está a ser criada";
                }
                else if ($arrayProp['value_type'] == "ent_ref"){
                    $nome = $this->db->runQuery("SELECT entity_name FROM entity WHERE id = ".$_REQUEST[$arrayProp['form_field_name']])->fetch_assoc()["entity_name"];
                    if ($nome == '') {
                        $valor = $_REQUEST[$arrayProp['form_field_name']];
                    }
                    else {
                        $valor = $nome;
                    }
                }
                else {
                    $valor = $_REQUEST[$arrayProp['form_field_name']];
                }
                
                $un = $this->obtemUnidades($arrayProp['unit_type_id'])
?>
                        <li>
<?php
                        //imprime o valor que o utilizador introduzio no formulario anterior para cada propriedade
                            echo $arrayProp['name'].": ".$valor." ".$un["name"];
?> 
                            <input type='hidden' name="<?php echo $arrayProp['form_field_name'];?>" value="<?php echo $_REQUEST[$arrayProp['form_field_name']];?>">
                        </li>
<?php
            }
            if ($tipo === "ent")
            {
                if (empty ($_REQUEST["nomeInst"])) {
                    $valor = "Não introduziu nenhum nome";
                }
                else {
                    $valor = $_REQUEST["nomeInst"];
                }
?>
                <li>Nome para instância da entidade: <?php echo $valor;?></li>
                <input type='hidden' name="nomeInst" value="<?php echo $_REQUEST['nomeInst'];?>">
<?php
            }
            else {
                foreach ($arrayEntidades as $id => $nome) {
                    if (empty ($_REQUEST["nomeInst_".$id])) {
                        $valor = "Não introduziu nenhum nome";
                    }
                    else {
                        $valor = $_REQUEST["nomeInst"];
                    }
?>
                    <li>Nome para instância da entidade <?php echo $nome; ?>: <?php echo $valor;?>
                    <input type="hidden" name="nomeInst_<?php echo $id; ?>" value="<?php echo $_REQUEST["nomeInst_".$id];?>">
<?php
                }
            }
?>
                    </ul>
                    </li>
                </ul>
               <input type="hidden" name="estado" value="inserir">
               <input type="submit" value="Submeter">
            </form>
<?php
        }  
    }
    
    /**
     * Method that gets the unit name of a given unit id
     * @param type $idUnit
     * @return string
     */
    private function obtemUnidades ($idUnit) {
        if(!is_null($idUnit))
        {
             $queryUn = "SELECT put.name FROM prop_unit_type AS put WHERE put.id = ".$idUnit;
             $resUn = $this->db->runQuery($queryUn);
             $un = $resUn->fetch_assoc();
        }
        else
        {
            $un["name"] = "";
        }
        return $un;
    }
    
    /**
     * Identifies all the entities/relations that are involved in a given form
     * @return an array of arrays with all the enities and all the relations
     */
    private function idEntRel($formId) {
        $guardaEnt = array();
        $guardaRel = array();
        $querySelProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$formId." AND prop.state = 'active' AND cfhp.property_id = prop.id "
                . "ORDER BY prop.fk_ent_type_id ASC, cfhp.field_order ASC";
        $resQuerySelProp = $this->db->runQuery($querySelProp);
        while ($prop = $resQuerySelProp->fetch_assoc()) {
            if (empty($prop["rel_type_id"])){
                $querySelEnt = "SELECT * FROM ent_type WHERE id = ".$prop["ent_type_id"];
                $resQuerySelEnt = $this->db->runQuery($querySelEnt);
                while ($ent = $resQuerySelEnt->fetch_assoc()) {
                    $guardaEnt[$ent["id"]] = $ent["name"];
                }    
            }
            else {
                $querySelRel = "SELECT * FROM rel_type WHERE id = ".$prop["rel_type_id"];
                $resQuerySelRel = $this->db->runQuery($querySelRel);
                while ($rel = $resQuerySelRel->fetch_assoc()) {
                   $guardaRel[$rel["id"]] = $rel["id"];
                }
            }
        }
        return [$guardaEnt,$guardaRel];
    }
    
}
// instantiation of an object from the class InsertValues. This instantiation is responsable to get the script work as expected.
new InsertValues();