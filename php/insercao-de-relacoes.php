<?php
require_once("custom/php/common.php");

$novaRelacao = new InsereRelacoes();

/**
 The methods that are present in this class will handle all the operations that we can do in 
 * the page inserção de relações
 *  @author fabio
 * 
 */
class InsereRelacoes
{
	private $bd;
        private $gereInsRel;
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
                $this->gereInsRel = new RelHist();
		$this->numProp = 0;
		$this->checkUser();
	}
	
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){
		$capability = 'insert_relation';
	
		if ( is_user_logged_in() )
		{
			if(current_user_can($capability))
			{
				if(empty($_REQUEST["estado"]))
				{
                                    $this->tablePrint();
				}
				else if($_REQUEST['estado'] == 'editar')
				{
                                    $this->editRlationProps();
				}
				else if($_REQUEST['estado'] == 'associar')
				{
                                    $this->associar();
				}
                                else if($_REQUEST['estado'] == 'introducao')
                                {
                                    $this->secondEntSel();
                                }
				else if($_REQUEST['estado'] == 'inserir')
				{
                                    $this->insertState();
				}
				else if($_REQUEST['estado'] == 'desativar')
				{
                                    $this->desactivate();
				}
                                else if($_REQUEST['estado'] == 'ativar')
                                {
                                    $this->activate();
                                }
			}
			else
			{
?>
				<html>
					<p>Não tem autorização para a aceder a esta página.</p>
				</html>
<?php 
			}
		}
		else 
		{
?>
			<html>
				<p>O utilizador não tem sessão iniciada.</p>
                                 <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
			</html>
<?php			
		}
	}
	
	/**
	 * This method will print the table that will be showing all the relations their state and the entity types that 
	 * will be associated to wich relation
	 */
	public function tablePrint()
        {                                
                                $res_Rel = $this->bd->runQuery("SELECT * From relation");
                                 if($res_Rel->num_rows == 0)
                                 {
?>
                                    <html>
                                        <p>Não existem relações.</p>
                                    </html>
<?php
                                 }
                                 else
                                 {
?>                                     
                            <html>
                                <table  class="table">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Tipo de relação</th>
                                            <th>Entidade 1</th>
                                            <th>Entidade 2</th>
                                            <th>Estado</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php
                                    while($readRelations = $res_Rel->fetch_assoc()){
                                         $res_EntPart = $this->bd->runQuery("SELECT ent_type1_id, ent_type2_id FROM rel_type WHERE id=".$readRelations['rel_type_id']);
                                         $read_EntPart = $res_EntPart->fetch_assoc();
                                        
                                         $res_name1 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_EntPart['ent_type1_id']);
                                         $read_name1 = $res_name1->fetch_assoc(); 
                                         $res_name2 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_EntPart['ent_type2_id']);
                                         $read_name2 = $res_name2->fetch_assoc();
?>                                         
                                        <tr>
                                             <td><?php echo $readRelations['id'];?></td>
                                             <td><?php echo $read_name1['name'];?> - <?php echo $read_name2['name'] ?></td>
                                             <td data-showHidden="true">
<?php 
                                                $_readEnt1 = $this->bd->runQuery("SELECT entity_name FROM entity WHERE id=".$readRelations['entity1_id'])->fetch_assoc();
                                                if($_readEnt1['entity_name'] != '')
                                                {
                                                    echo $_readEnt1['entity_name'];
                                                }
                                                else
                                                {
                                                    echo $readRelations['entity1_id'];
                                                }
                                                
						$sanitizeId = $this->bd->userInputVal($readRelations['entity1_id']);
						$res_GetVal = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
?>
<?php
                                                $count = 0;
                                             	while($read_Props = $res_GetVal->fetch_assoc())
                                             	{
                                             		$nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];
?>
                                                        <p hidden="hidden"><span><?php echo $nome." : ".$read_Props['value']."\n"; $count++;?></span></p>										
<?php
                                             	}
                                                if($count == 0)
                                                {
                                                    ?>
                                                    <p hidden="hidden"><span>Não existem valores de propriedades para esta entidade</span></p>
                                                 <?php
                                                }                                  
?>                           
                                             </td>
                                           
                                             <td data-showHidden="true">
<?php   
                                            $_readEnt2 = $this->bd->runQuery("SELECT entity_name FROM entity WHERE id=".$readRelations['entity2_id'])->fetch_assoc();
                                            if($_readEnt2['entity_name'] != '')
                                            {
                                                echo $_readEnt2['entity_name'];
                                            }
                                            else
                                            {
                                                echo $readRelations['entity2_id'];
                                            }
                                             
                                             $sanitizeId = $this->bd->userInputVal($readRelations['entity2_id']);
                                             $res_GetVal = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
                                             $count = 0;
                                             while($read_Props = $res_GetVal->fetch_assoc())
                                             {
                                             	$nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];
?>
                                                <p hidden="hidden"><span><?php echo $nome." : ".$read_Props['value']."\n"; $count++ ?></span></p>												
<?php
                                             }
                                             if($count == 0)
                                             {
?>
                                                 <p hidden="hidden"><span>Não existem valores de propriedades para esta entidades</span></p>
<?php                                       }
?>                         
                                             </td>
<?php
                                                if($readRelations['state'] == 'active')
                                                {
?>       
                                                        <td>Ativo </td>
                                                        <td>
                                                            <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Inserir/Editar Propriedades da Relação]</a>  
                                                            <a href="insercao-de-relacoes?estado=desativar&rel=<?php echo $readRelations['id'];?>">[Desativar]</a>
							</td>
<?php
                                                } 
                                                else
                                                {
?>
                                                    <td>Inativo</td>
                                                    <td>
                                                        <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Inserir/Editar Propriedades da Relação]</a>  
                                                        <a href="insercao-de-relacoes?estado=ativar&rel=<?php echo $readRelations['id'];?>">[Ativar]</a>
                                                   </td>
<?php   
                                                }
?>
                                         </tr>
<?php
                                     }
                                 }
?>                               
                                </tbody>
                            </table>   
            </html>

<?php
        $this->createNewRel();
        }
        
        
	
        /**
	 * This will make you edit values for the selected relation or add the value 
         * prints two tables 
         * one with all the possible properties you can add to your relation depending on the rel_type
         * and the other with the existing properties and their values in your ralation
	 */
        public function editRlationProps(){
            //get relation type from the relation selected
            $res_relTypeId = $this->bd->runQuery("SELECT rel_type_id FROM relation WHERE id=".$_REQUEST['rel']);
            $read_relTypeId = $res_relTypeId->fetch_assoc();
            
            //get the properties that are associated to the rel_type where the previousçy selected relation belongs. 
            $res_GetPropFromRelType = $this->bd->runQuery("SELECT * FROM property WHERE rel_type_id=".$read_relTypeId['rel_type_id']);
            
            
            $res_PropAded = $this->bd->runQuery("SELECT * FROM value WHERE relation_id=".$_REQUEST['rel']);
            
            
            //Show a table with properties who can be added.
            if($res_PropAded->num_rows != $res_GetPropFromRelType->num_rows)//se o numero de instancias de propriedades de uma relação é menor que o numero de propriedades 
                //não é igual ao numero de propriedades da tabela propertyy significa que ainda posso adicionar mais propriedades
            {
?>
                <h3>Inserção de Relações - Inserção de propriedades das relações</h3>
<?php
                $this->possibleValuesToAdd($read_relTypeId['rel_type_id'],$_REQUEST['rel']);
            }
            else
            {
?>
                        <html>
                             <h3>Inserção de Relações - Inserção de propriedades das relações</h3>
                            <p>Não existem propriedades que possam ser adicionadas.</p>
                        </html>
<?php
            }
           
            //Show a table with all the property values associated to the selected relation
?>
            <h3>Inserção de Relações - Alteração de propriedades das relações</h3>
    <?php   
           $res_GetPropRel = $this->bd->runQuery("SELECT * FROM value WHERE relation_id = ".$_REQUEST['rel']);
           if($res_GetPropRel->num_rows == 0)
           {
?>               
               <html>
                <p>Não existem propriedades associadas a esta relação.</p>
                <p>Pelo que não pode efetuar nenhuma alteração.</p>
               </html>
 <?php
           }
           else
           {
               $this->changePropValue($read_relTypeId['rel_type_id'],$_REQUEST['rel'],$res_GetPropRel);
           }
            
            
            
        } 
       
        /**
         * This method prints a table with all the property values that are no assigned to the selectd relation
         * @param type $res_PropAded
         * @param type $res_GetPropFromRelType
         */
        private function possibleValuesToAdd($tipyRelSel,$idDaRel)
        {
            
?>
                        <html>
                            <form>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>Id</td>
                                        <td>Nome propriedade</td>
                                        <td>Tipo</td>
                                        <td>Seleção</td>
                                        <td>Novo valor</td>
                                    </tr>
                                </thead>
                                <tbody>
<?php
                                $conta = 0;
                                $res_CanBeAdded = $this->bd->runQuery("SELECT DISTINCT p.id, p.name, p.value_type FROM value as v, property as p WHERE p.rel_type_id=".$tipyRelSel." AND p.id NOT IN (SELECT value.property_id FROM value WHERE value.relation_id = ".$idDaRel.") ");
                               while($read_CanBeAdded = $res_CanBeAdded->fetch_assoc())
                                {
?>                                  
                                    <tr>
                                        <td><?php echo $read_CanBeAdded['id']; ?></td>
                                        <td><?php echo $read_CanBeAdded['name']; ?></td>
                                        <td><?php  echo $read_CanBeAdded['value_type'];?></td>
                                        <td><input type="checkbox" name="check<?php echo $conta; ?>" value="<?php echo $read_CanBeAdded['id']?>"></td>
                                        <td>
<?php
                                            //verifies the value type
                                            if($read_CanBeAdded['value_type'] == 'bool')
                                            {
?>
                                                <input type="radio" name="<?php echo 'radio'.$conta ?>" value="true">True
                                                <input type="radio" name="<?php echo 'radio'.$conta ?>" value="false">False
<?php
                                            }
                                            else if($read_CanBeAdded['value_type'] == 'enum')
                                            {   
                                                $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_CanBeAdded['id']);
?>
                                                <select name="<?php echo 'select'.$conta ?>">
<?php
                                                while($read_EnumValue = $res_EnumValue->fetch_assoc())
                                                {
?>
                                                    <option  value="<?php echo $read_EnumValue['value']; ?>"><?php echo $read_EnumValue['value']; ?></option>
<?php
                                                }
?>
                                                </select>
<?php
                                            }
                                            else
                                            {
?>
                                                <input type="text" name="<?php echo 'textbox'.$conta ?>">
                                            
<?php
                                            }
                                            $conta++;
                                            
?>          
                                        </td>
                                    </tr>
<?php
                                }
                                $_SESSION['propImpressas'] = $conta;
?>                             
                                </tbody>
                            </table>
                                    <input type="hidden" name="iddarel" value="<?php echo $idDaRel ?>" >
                                    <input type="hidden" name="flag" value="atributosNovos">
                                    <input type="hidden" name="estado" value="inserir">
                                    <input type="submit" value="Adicionar Novas Propriedades">
                            </form>
                        </html>
<?php
        }
        
        /**
         * This method prints a table with all the properties that are associted to the select relation
         * @param type $tipyRelSel
         * @param type $idDaRel
         * @param type $res_GetPropRel -> mysqli object
         */
        private function changePropValue($tipyRelSel,$idDaRel,$res_GetPropRel)
        {
?>
            <html>
                <form>
                    <table class="table">
                        <thead>
                            <tr>
                                <td>Id</td>
                                <td>Nome propriedade</td>
                                <td>Tipo</td>
                                <td>Valor Atual </td>
                                <td>Seleção</td>
                                <td>Novo valor</td>
                            </tr>
                        </thead>
                        <tbody>
<?php
                            $conta = 0;
                            while($read_GetPropRel = $res_GetPropRel->fetch_assoc())
                            {
                                $res_Prop= $this->bd->runQuery("SELECT p.id, p.name, p.value_type FROM property as p WHERE id =".$read_GetPropRel['property_id']);
                                $read_PropValues = $res_Prop->fetch_assoc();
?>
                            <tr>
                                <td><?php echo $read_PropValues['id'];?></td>
                                <td><?php echo $read_PropValues['name']?></td>
                                <td><?php echo $read_PropValues['value_type']?></td>
                                <td><?php echo $read_GetPropRel['value'] ?></td>
                                <td><input type="checkbox" name="check<?php echo $conta; ?>" value="<?php echo $read_GetPropRel['id']?>"></td>
                                <td>
<?php
                                    if($read_PropValues['value_type'] == 'bool')
                                    {
                                        
?>
                                        <input type="radio" name="<?php echo 'radio'.$conta ?>" value="true">True
                                        <input type="radio" name="<?php echo 'radio'.$conta ?>" value="false">False
<?php
                                    }
                                    else if($read_PropValues['value_type'] == 'enum')
                                    {   
                                        $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_GetPropRel['property_id']);
?>
                                        <select name="<?php echo 'select'.$conta ?>">
<?php
                                        while($read_EnumValue = $res_EnumValue->fetch_assoc())
                                        {
?>
                                            <option  value="<?php echo $read_EnumValue['value']; ?>"><?php echo $read_EnumValue['value']; ?></option>
<?php
                                        }
?>
                                        </select>
<?php                                    
                                    }
                                    else
                                    {
?>
                                        <input type="text" name="<?php echo 'textbox'.$conta ?>">
<?php  
                                    }
?>                                    
                                </td>
                            </tr>
<?php   
                                $conta++;
                            }
                            $_SESSION['attrDaRelImp'] = $conta;
?>
                        </tbody>
                    </table>
                
                    <input type="hidden" name="iddarel" value="<?php echo $idDaRel ?>" >
                    <input type="hidden" name="flag" value="UpdateAttr">
                    <input type="hidden" name="estado" value="inserir">
                    <input type="submit" value="Atualizar Propriedades">
                </form>
           </html>
<?php                    
        }
        
	/**
         * Associates the newy creaed value and associates it with another 
         * existing value.
         */
	public function associar(){
            if(empty($this->bd->userInputVal($_REQUEST['ent'])))
            {
?>
                 <h3>Inserção de Relações - Lista Tipos de relação</h3>
                  <p>Não selecionou uma entidade.</p>
                  <p>Clique em <?php goBack(); ?></p>
<?php
                 exit;
            }
            else {
                if(is_numeric($_REQUEST['ent']))
                {
                }
                else
                {
                    //this comes from the bottom it cuts out of the string the url that is used to make the ajax pushes
                    
                  $_REQUEST['ent'] = substr_replace($this->bd->userInputVal($_REQUEST['ent']),"",0,12);    
                }
                
                
                
               
                //print_r($_REQUEST);
               
            }
            echo "bef";
            echo $_REQUEST['ent'];
            echo "aft;";
            //after
            $res_EntType = $this->bd->runQuery("SELECT * FROM entity WHERE id=".$this->bd->userInputVal($_REQUEST['ent']));
            $read_EntType = $res_EntType->fetch_assoc();
            //print_R($res_EntType);
            $res_RelTypes = $this->bd->runQuery("SELECT * FROM rel_type WHERE ent_type1_id=".$read_EntType['ent_type_id']." OR ent_type2_id=".$read_EntType['ent_type_id']);
            //echo "SELECT * FROM rel_type WHERE ent_type1_id=".$read_EntType['ent_type_id']." OR ent_type2_id=".$read_EntType['ent_type_id'];
            if($res_RelTypes->num_rows == 0 )
            {
?>
                 <h3>Inserção de Relações - Lista Tipos de relação</h3>
                 <p>Não existem tipos de relação aos quais a entidade selecionada possa ser associada.</p>
                 <p>Clique em <?php goBack(); ?> e selecione outra entidade.</p>
<?php
            }
            else
            {
 ?>          
            <h3>Inserção de Relações - Lista Tipos de relação</h3>
            <html>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Tipo de Relacao</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                        while($read_RelTypes = $res_RelTypes->fetch_assoc())
                        {
                            $res_name1 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_RelTypes['ent_type1_id']);
                            $read_name1 = $res_name1->fetch_assoc(); 
                            $res_name2 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_RelTypes['ent_type2_id']);
                            $read_name2 = $res_name2->fetch_assoc(); 
?>
                        <tr>
                            <td><?php echo $read_RelTypes['id']?></td>
                            <td>
                                <a href="insercao-de-relacoes?estado=introducao&ent=<?php echo $_REQUEST['ent']; ?>&rel_type=<?php echo $read_RelTypes['id'];?>">[<?php echo $read_name1['name'];?> - <?php echo $read_name2['name'];?>]</a>
                            </td>
                        </tr>
<?php
                        }
?>
                    </tbody>
                </table>
            </html>
 <?php         
            }
        }
        
        /**
         * This method will be able to prvide the user a list of entities that are compatible with the 
         * relation_type choosen in the associar state
         */
        public function secondEntSel(){
            $prev_SelEnt = $_REQUEST['ent'];
            $sltd_RelType = $_REQUEST['rel_type'];
            
            $res_CompRel = $this->bd->runQuery("SELECT * FROM rel_type WHERE id=".$sltd_RelType);
            $read_CompRel =$res_CompRel->fetch_assoc();
            
            $res_InsType = $this->bd->runQuery("SELECT * FROM entity WHERE id=".$prev_SelEnt);
            $read_InsType = $res_InsType->fetch_assoc();
            
            if($read_CompRel['ent_type1_id'] == $read_InsType['ent_type_id'])
            {
               $res_SencondEnt =  $this->bd->runQuery("SELECT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type2_id = entity.ent_type_id AND rel_type.ent_type2_id=".$read_CompRel['ent_type2_id']);
?>
                <html>
                    <form>
                        <table class="table">
                            <thead>
                            <th>Entidades que podem ser associadas</th>
                            <th>Nome da relação</th>
                            </thead>
                            <tbody>
<?php
                                $control = 0;
                                while($read_SecondEnt = $res_SencondEnt->fetch_assoc())
                                {
                                    if($read_SecondEnt['entity_name'] != '')
                                    {
?>
                                    <tr>
                                        <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo $read_SecondEnt['entity_name']; ?></td>
                                        <td> <!--<label>Nome para a relação </label>--><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                    <tr>
<?php
                                    }
                                    else
                                    { 
                                        //if the user didn't fave any name to the entity e need to search for the attribute of that entity who has a name.
?>
                                    <tr>
                                        <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo  $read_SecondEnt['id']; ?></td>
                                        <td> <!--<label>Nome para a relação </label>--><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                    <tr>
<?php
                                    }
                                    $control++;
                                }
                                $_SESSION['numEnt2Max'] = $control; 
?>
                            </tbody>
                        </table>
                                <input type="hidden" name="rel_type" value="<?php echo $sltd_RelType;?>">
                                <input type="hidden" name="firstEnt" value="<?php echo  $prev_SelEnt?>">

                                <input type="hidden" name="flag" value="naoeditar">
                                <input type="hidden" name="estado" value="inserir">
                                <input type="submit" value="Associar Segunda Entidade">
                    </form>
                </html>
<?php               
            }
            else if( $read_CompRel['ent_type2_id'] == $read_InsType['ent_type_id'])
            {
                $res_SencondEnt =  $this->bd->runQuery("SELECT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type1_id = entity.ent_type_id  AND rel_type.ent_type1_id=".$read_CompRel['ent_type1_id']);
?>
                <html>
                    <form>
                        <table class="table">
                            <thead>
                            <th>Entidades que podem ser associadas</th>
                            <th>Nome da relação</th>
                            </thead>
                            <tbody>
<?php
                            $control = 0;
                            while($read_SecondEnt = $res_SencondEnt->fetch_assoc())
                            {
                                if($read_SecondEnt['entity_name'] != '')
                                {
        ?>
                                <tr>
                                    <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo $read_SecondEnt['entity_name']; ?></td>
                                    <td><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                </tr>
        <?php   
                                }
                                else
                                {               //if the user didn't fave any name to the entity e need to search for the attribute of that entity who has a name.
?>
                                    <tr>
                                        <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo  $read_SecondEnt['id']; ?></td>
                                        <td> <!--<label>Nome para a relação </label>--><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                    <tr>
<?php                                    

                                }
                                $control++;
                            }
                            $_SESSION['numEnt2Max'] = $control; 
        ?>
                                    </tbody>
                        </table>

                             <input type="hidden" name="rel_type" value="<?php echo $sltd_RelType;?>">
                            <input type="hidden" name="firstEnt" value="<?php echo  $prev_SelEnt?>">

                            <input type="hidden" name="flag" value="naoeditar">
                            <input type="hidden" name="estado" value="inserir">
                            <input type="submit" value="Associar Segunda Entidade">
                    </form>
                </html>
<?php                              
            }
            else
            {
?>
                <html>
                    <p>Erro, o tipo de relação selecionado anteriormente não é compatível com a entidade criada.</p>
                </html>
<?php
            }
            
        }
	
	/**
	 * This method will activate the relation the user selected.
	 */
	public function activate(){
            
            $idRel = $this->bd->userInputVal($_REQUEST['rel']);
            if( $this->gereInsRel->addHist($idRel,$this->bd))
            {
                if($this->bd->runQuery("UPDATE relation SET state='active' WHERE id=".$idRel))
                {
?>
                   <html>
                        <p>A relação foi ativada.</p>
                        <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                   </html>
<?php
                 $this->bd->getMysqli()->commit();
                }
                else
                {
?>
                <html>
                    <p>A ativação da relação falhou.</p>
                    <p>Clique em <?php goBack();?>.</p>
                </html>
<?php
                 $this->bd->getMysqli()->rollback();
                }
            }
            else
            {
?>
                <html>
                    <p>A ativação da relação falhou.</p>
                    <p>Clique em <?php goBack();?>.</p>
                </html>
<?php   
            $this->bd->getMysqli()->rollback();
            }
        }
        
        /**
	 * This method will desactivate the relation the user selected.
	 */
	public function desactivate(){
             $idRel = $this->bd->userInputVal($_REQUEST['rel']);
            if( $this->gereInsRel->addHist($idRel,$this->bd))
            {
                if($this->bd->runQuery("UPDATE relation SET state='inactive' WHERE id=".$idRel))
                {
?>
                    <html>
                        <p>A relação foi desativada.</p>
                        <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                    </html>
<?php
                $this->bd->getMysqli()->commit();
                }
                else
                {
?>
                    <html>
                        <p>A desativação da relação falhou.</p>
                         <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php
                 $this->bd->getMysqli()->rollback();
                }
            }
            else
            {
?>
                    <html>
                        <p>A desativação da relação falhou.</p>
                         <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php             
            $this->bd->getMysqli()->rollback();
            }
        }
	
	/**
	 * This method will handle the insertion that a user will make in the database.
	 */
	public function insertState()
        {
            if($this->ssvalidation())
            {
                if($_REQUEST['flag'] == 'naoeditar')
                {
                    $this->nedita();
                }
                else if ($_REQUEST['flag'] =='atributosNovos')
                {
                    $this->addNewAttr();
                }
                else if($_REQUEST['flag'] =='UpdateAttr')
                {
                    $this->updateAttr();
                }
            }
            else 
            {
                goBack();
            }
        }
        
        /**
         * Updates the current value from the properties to new ones.
         */
        private function updateAttr()
        {
            for($i= 0; $i <= $_SESSION['attrDaRelImp']; $i++ )
            {
                
                if(isset($_REQUEST['check'.$i]))
                {
                    if(isset($_REQUEST['radio'.$i]))
                    {
                        $newValue = $_REQUEST['radio'.$i];
                    }
                    else if(isset($_REQUEST['select'.$i]))
                    {
                        $newValue = $_REQUEST['select'.$i];
                        //echo $_REQUEST['select'.$i];
                    }
                    else if(isset($_REQUEST['textbox'.$i]))
                    {
                        $newValue =$_REQUEST['textbox'.$i];
                    }
                     $id = $this->bd->userInputVal($_REQUEST['iddarel']);
                    if($this->gereInsRel->addHist($id, $this->bd))
                    {
                        if($this->bd->runQuery("UPDATE `value` SET `value`='".$newValue."', `updated_on`='".date("Y-m-d H:i:s",time())."' WHERE id=".$_REQUEST['check'.$i]))
                        {
?>
                            <html>
                                <p>A propriedades foi atualizada</p>
                                <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                            </html>
<?php
                            $this->bd->getMysqli()->commit();
                        }
                        else
                        {
?>
                            <html>
                                <p>Ocorreu um erro pelo que a propriedade não foi atualizada</p>
                                <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                            </html>
<?php
                            $this->bd->getMysqli()->rollback();
                        }
                    }
                    else
                    {
?>
                            <html>
                                <p>Ocorreu um erro pelo que a propriedade não foi atualizada</p>
                                <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                            </html>
<?php                       
                    $this->bd->getMysqli()->rollback();
                    }
                     
                }
            }
        }
        
        /**
         *This method will insert new atributtes 
         */
        private function addNewAttr()
        {
            for($i= 0; $i <= $_SESSION['propImpressas']; $i++ )
            {
                
                if(isset($_REQUEST['check'.$i]))
                {
                    if(isset($_REQUEST['radio'.$i]))
                    {
                        $newValue = $_REQUEST['radio'.$i];
                    }
                    else if(isset($_REQUEST['select'.$i]))
                    {
                        $newValue = $_REQUEST['select'.$i];
                        //echo $_REQUEST['select'.$i];
                    }
                    else if(isset($_REQUEST['textbox'.$i]))
                    {
                        $newValue =$_REQUEST['textbox'.$i];
                    }
                    $id = $this->bd->userInputVal($_REQUEST['iddarel']);
                    if($this->gereInsRel->addHist($id, $this->bd))
                    {
                        if($this->bd->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `state`, `updated_on`) VALUES (NULL,NULL,".$_REQUEST['check'.$i].",'".$newValue."','".wp_get_current_user()->user_login."',".$id.",'active','".date("Y-m-d H:i:s",time())."')"))
                        {
?>
                            <html>
                                <p>As propriedades foram adicionadas à relação.</p>
                                <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                            </html>
<?php
                        $this->bd->getMysqli()->commit();
                        }
                        else
                        {
?>
                            <html>
                                <p>Erro ao Adicionar as propriedades à relação.</p>
                                <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                            </html>
<?php
                         $this->bd->getMysqli()->rollback();
                        }
                    }
                    else
                    {
?>
                            <html>
                                <p>Erro ao Adicionar as propriedades à relação.</p>
                                <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                            </html>
<?php  
                        $this->bd->getMysqli()->rollback();
                    }
                    

                    
                }
            }
        }
        
        
        /**
         * Mehtod that will insert new values in the database.
         */
        private function nedita(){
            //a preencher
            
            for($i=0; $i <= $_SESSION['numEnt2Max'];$i++){    
                
                if(isset($_REQUEST['nomeDaRel'.$i]))
                {
                     $rel_name= $this->bd->userInputVal($_REQUEST['nomeDaRel'.$i]);
                      
                }
                else
                {
                    $rel_name="";
                }
                if(isset($_REQUEST['secondEnt'.$i])){
                    if($this->bd->runQuery("INSERT INTO `relation`(`id`, `rel_type_id`, `entity1_id`, `entity2_id`, `relation_name`, `state`) VALUES (NULL,".$_REQUEST['rel_type'].",".$_REQUEST['firstEnt'].",".$_REQUEST['secondEnt'.$i].",'".$rel_name."','active')"))
                    {
                        
?>
                        <html>
                            <p>Associou com sucesso a entidade <?php echo $_REQUEST['firstEnt']; ?>, a entidade <?php echo $_REQUEST['secondEnt'.$i]; ?>.</p>
                            <p>Clique em <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $this->bd->getMysqli()->insert_id; ?>"/>Inserir Propriedades</a> para preencher informações relativas a relação que acabou de criar.</p>
                        </html>
 <?php
                    }
                    else
                    {
?>
                        <html>
                            <p>A inserção de esta nova relação falhou.</p>
                            <p>Clique em <a href="insercao-de-relacoes"/>Continuar</a> para continuar</p>
                        </html>
 <?php
                    }
                }
        
                
            }
        }
        
        /**
         * Check if a value is an integer or a bool or a double.
         * @param type $value_type ->type of that value
         * @param type $valores -> value to checl
         * @return boolean
         */
        private function typeValidation($value_type,$valores){
            switch($value_type) {
                case 'int':
                    if(ctype_digit($valores))
                    {
                        $valores = (int)$valores;
                        $tipoCorreto = true;
                    }
                    else
                    {
    ?>
                        <p>O valor introduzido não está correto. Certifique-se que introduziu um valor numérico</p>
    <?php
                        $tipoCorreto = false;

                    }
                break;
                case 'double':
                    if(is_numeric($valores))
                    {
                        $valores = floatval($valores);
                        $tipoCorreto = true;
                    }
                    else
                    {
?>
                        <p>O valor introduzido não está correto. Certifique-se que introduziu um valor numérico</p>
<?php
                        $tipoCorreto = false;
                    }
                break;
                case 'bool':
                    if($valores == 'true' || $valores == 'false')
                    {
                        $valores = boolval($valores);
                        $tipoCorreto = true;
                    }
                    else
                    {
?>
                        <p>O valor introduzido para o campo <?php echo $propriedadesExcel[$i];?> não está correto. Certifique-se que introduziu um valor true ou false</p>
<?php
                        $tipoCorreto = false;
                    }
                    break;
                default:
                    $tipoCorreto = true;
                    break;
            }
            return $tipoCorreto;
        }
        /**
	 * Server side validation when JQuery is disabled
	 */
	public function ssvalidation(){
            if($_REQUEST['flag'] == 'UpdateAttr')
            {
                //validation you should select at least one checkbox
               //validation check if for every check box there is at least one value 
                $count = 0;
                for($i=0; $i <= $_SESSION['attrDaRelImp']; $i++)
                    {
                        if(isset($_REQUEST['check'.$i]))
                        {
                            //echo $i;
                            //there is no 
                            //echo $_REQUEST['textbox'.$i];
                            if(empty($_REQUEST['select'.$i]) && empty($_REQUEST['radio'.$i]) && empty($_REQUEST['textbox'.$i]))
                            {
?>
                        <html>
                            <p>Verifique se para todas as checkBoxes selecionadas introduziu valores.</p>
                        </html>
<?php
                                return false;
                            }
                            else
                            {
                                if(isset($_REQUEST['select'.$i]))
                                {}
                                else if(isset($_REQUEST['radio'.$i]))
                                {}
                                else if(isset($_REQUEST['textbox'.$i]))
                                {
                                    $res_getPropId = $this->bd->runQuery("SELECT property_id FROM value WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$i]));
                                    $getPropId = $res_getPropId->fetch_assoc();
                                    
                                    $res_getValue_Type = $this->bd->runQuery("SELECT value_type FROM property WHERE id=".$getPropId['property_id']);
                                    $getValue_Type = $res_getValue_Type->fetch_assoc();
                                   
                                    if($this->typeValidation($getValue_Type['value_type'], $this->bd->userInputVal($_REQUEST['textbox'.$i]))== false)
                                    {
                                        ?>
                        <html>
                            <p>Verifique se o tipo introduzido num dos campos é compativel com o valor aceite na base de dados.</p>
                        </html>
                        
                                            <?php
                                        return false;
                                    }
                                }
                            }
                            $count++;
                        }
                    }
                    
                if($count == 0)
                {
?>
                    <html>
                        <p>Deve selecionar pelo menos uma propriedade para atualizar</p>
                    </html>
<?php
                    return false;
                }
                    return true;
                    
                    
                }
            else if($_REQUEST['flag'] == 'atributosNovos')
                {
                    $count = 0;
                    for($i=0; $i <=  $_SESSION['propImpressas']; $i++)
                    {
                        if(isset($_REQUEST['check'.$i]))
                        {
                           // echo $i;
                            if(empty($_REQUEST['select'.$i]) && empty($_REQUEST['radio'.$i]) && empty($_REQUEST['textbox'.$i]))
                            {
?>
                                <html>
                                    <p>Verifique se para todas as checkBoxes selecionadas introduziu valores.</p>
                                </html>
<?php
                                return false;
                            }
                            else
                            {
                                if(isset($_REQUEST['select'.$i])){}
                                else if(isset($_REQUEST['radio'.$i])){}
                                else if(isset($_REQUEST['textbox'.$i]))
                                {                                    
                                    $res_getValue_Type = $this->bd->runQuery("SELECT value_type FROM property WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$i]));
                                    $getValue_Type = $res_getValue_Type->fetch_assoc();
                                   
                                    if($this->typeValidation($getValue_Type['value_type'], $this->bd->userInputVal($_REQUEST['textbox'.$i]))== false)
                                    {
?>
                                        <html>
                                            <p>Verifique se o tipo introduzido num dos campos é compativel com o valor aceite na base de dados.</p>
                                        </html>                        
<?php
                                        return false;
                                    }
                               }
                            }
                            $count++;
                        }
                    }
                    
                        if($count == 0)
                        {
?>
                            <html>
                                 <p>Deve selecionar pelo menos uma propriedade para atualizar</p>
                            </html>
<?php
                            return false;
                        }
                        return true;
                    }
            else if($_REQUEST['flag'] == 'naoeditar')
            {
                        return true;
            }
        }
        
        /**
         * Allow's the user to create a new relation with the existing entities.
         */
        private function createNewRel(){
?>
            <h3>Inserção de Relações - Nova Relação</h3>
            <form>
                <select id="ent" name="ent">
                        <option></option>
<?php
                    $res_GetEntities = $this->bd->runQuery("SELECT * FROM entity");
                    while($read_GetEnt = $res_GetEntities->fetch_assoc()){
 
                        if($read_GetEnt['entity_name'] == '')
                        {
?>
                            <option value="getAttr?ent=<?php echo $read_GetEnt['id']?>"><?php echo $read_GetEnt['id']?></option>
<?php
                        }
                        else
                        {
?>
                            <option value="getAttr?ent=<?php echo $read_GetEnt['id']?>"><?php echo $read_GetEnt['entity_name']?></option>
<?php                            
                        }
                       
                    }
?>
                </select>
                
                <div id="showAttr" type="hidden">
                    
                </div>
                
                <noscript></noscript>
                
                <input type="hidden" name="estado" value="associar">
                <input type="submit" value="Inserir nova Relação">
            </form>
<?php
        
            //mandar um ent com o id da propriedade selecionada para o associar

            
        }
        
        
        
}

/**
 * This class has all the methods to manage all the history of the table hist_relation
 */
class RelHist{
    
    public function __construct(){}
    /**
     * This method will make a backup from all the change that are made in the relations
     * @param type $id -> id form the selected relation
     * @param type $bd -> is an object that will allow ius to use the database functions
     * @return boolean
     */
    public function addHist($id,$bd){
        $bd->getMysqli()->autocommit(false);
	$bd->getMysqli()->begin_transaction();
        
        $res_oldRel = $bd->runQuery("SELECT * FROM relation WHERE id=".$id);
        if($res_oldRel->num_rows == 1)
        {
            $inactive = date("Y-m-d H:i:s",time());
            $read_oldRel = $res_oldRel->fetch_assoc();
            if($bd->runQuery("INSERT INTO `hist_relation`(`id`, `rel_type_id`, `entity1_id`, `entity2_id`, `relation_name`, `state`, `relation_id`, `active_on`, `inactive_on`) "
                    . "VALUES (NULL,".$read_oldRel['rel_type_id'].",".$read_oldRel['entity1_id'].",".$read_oldRel['entity2_id'].",'".$read_oldRel['relation_name']."','".$read_oldRel['state']."',".$id.",'".$read_oldRel['updated_on']."','".$inactive."')"))
            {
                
              $resSVal = $bd->runQuery("SELECT * FROM value  WHERE relation_id=".$id);
              while($readSVal = $resSVal->fetch_assoc())
              {
                  if($readSVal['entity_id']=='')
                  {
                      if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,NULL,".$readSVal['property_id'].",'".$readSVal['value']."','".$readSVal['producer']."',".$id.",".$readSVal['id'].",'".$readSVal['updated_on']."','".$inactive."','".$readSVal['state']."')"))
                      {
                        return false;
                      }
                  }
                  else
                  {
                      if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readSVal['entity_id'].",".$readSVal['property_id'].",'".$readSVal['value']."','".$readSVal['producer']."',".$id.",".$readSVal['id'].",'".$readSVal['updated_on']."','".$inactive."','".$readSVal['state']."')"))
                      {
                        return false;
                      }
                  }
                  
                 
              }
                
               return true;
            }
           
        }
        return false;    
    }
    
    
    
}
?>
