<?php
/*
Esse script funciona como um front controller, todas as requisições passam primeiro por aqui, também podemos enxergar como um gateway padrão. Isso só é possível graças ao htaccess que faz com que o todas as requisições feitas sejam redirecionadas para cá.
Da forma como esse arquivo de rotas funciona, nós não fazemos “links” para arquivos, nós associamos uma url a um controller.
****Descomentar os print_r abaixo para entender melhor****
*/

//Path é um array onde cada posição é um elemento da URL
$path = explode('/', $_SERVER['REQUEST_URI']);
//Action é a posição do array
$action = $path[sizeOf($path) - 1];
//Caso a ação tenha param GET esse param é ignorado, isso é particularmente útil para trabalhar com AJAX, já que o conteúdo do get será útil apenas para o controller e não para a rota
$action = explode('?', $action);
$action = $action[0];

//Descomentar esse bloco e acessar qualquer url do sistema.
/*echo "<pre>";
echo "A URL digitada<br>";
print_r($_SERVER['REQUEST_URI']);
echo "<br><br>A URL digitada explodida por / e tranformada em um array<br>";
print_r($path);
echo "<br><br>A ultima posição do array, que é a ação que o usuário/sistema quer realizar, é essa ação(string) que é mapeada(roteada) a um método de um controller<br>";
print_r($action);
echo "</pre>";*/

//Todo controller que tiver pelo menos uma rota associada a ele deve aparecer aqui.
include_once $_SESSION["root"].'php/Controller/ControllerLogin.php';
include_once $_SESSION["root"].'php/Controller/ControllerFuncionario.php';
include_once $_SESSION["root"].'php/Controller/ControllerDepartment.php';
include_once $_SESSION["root"].'php/Controller/ControllerProject.php';

//Sequencia de condicionais que verificam se a ação informada está roteada
if ($action == '' || $action == 'index' || $action == 'index.php' || $action == 'login') {
	require_once $_SESSION["root"].'php/View/ViewLogin.php';
} else if ($action == 'postLogin') {
	$cLogin = new ControllerLogin();
	$cLogin->verificaLogin();
} else if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
	$_SESSION['flash']['msg'] = 'You are not yet logged in';
	$_SESSION['flash']['sucesso'] = false;
	header('Location: login');
} else if ($action == 'exibeFuncionarios') {
	$cFunc = new ControllerFuncionario();
	$cFunc->getAllFuncionarios();
} else if ($action == 'cadastraFuncionario' && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
	$depDAO = new DepartmentDAO();
	$departments=$depDAO->getAllDepartments();
	require_once $_SESSION["root"].'php/View/ViewCadastraFuncionario.php';
} else if ($action == 'cadastrarDepartamento' && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
	require_once $_SESSION["root"].'php/View/ViewRegisterDepartment.php';
} else if ($action == 'postCadastraFuncionario') {
	$cFunc = new ControllerFuncionario();
	$cFunc->setFuncionario();
	header('Location: exibeFuncionarios');
} else if ($action == 'postEditaFuncionario') {
	$cFunc = new ControllerFuncionario();
	$cFunc->updateFuncionario();
	header('Location: exibeFuncionarios');
} else if ($action == 'postCadastraDepartment') {
	$cFunc = new ControllerDepartment();
	$cFunc->setDepartment();
} else if ($action == 'visualizarDepartamento') {
	$cFunc = new ControllerDepartment();
	$cFunc->getAllDepartments();
} else if ($action == 'edit' ) {
	if(!(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']))
		header('Location: exibeFuncionarios');
	else{
		$funcDAO = new FuncionarioDAO();
		$funcionario = $funcDAO->getFuncionario($_POST['login']);
		$depDAO = new DepartmentDAO();
		$departments=$depDAO->getAllDepartments();
		require_once $_SESSION['root'].'php/View/ViewEditaFuncionario.php';
	}
} else if ($action == 'disable') {
	if(!(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']))
		header('Location: exibeFuncionarios');
	else{
		$funcDAO = new FuncionarioDAO();
		$funcionario = $funcDAO->getFuncionario($_POST['login']);
		$funcionario = $funcDAO->disableFuncionario($funcionario);
		header('Location: exibeFuncionarios');
	}
} else if ($action == 'logout') {
	session_destroy();
	header('Location: login');
} else if ($action == 'sortByName') {
	$_SESSION["sort"] = "Name";
	header("Location: exibeFuncionarios");
} else if ($action == 'sortBySalary') {
	$_SESSION["sort"] = "Salary";
	header("Location: exibeFuncionarios");
} else if ($action == 'sortByLogin') {
	$_SESSION["sort"] = "Login";
	header("Location: exibeFuncionarios");
} else if ($action == 'sort') {
	header("Location: visualizarDepartamento");
} else if ($action == 'cadastrarProjeto' && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
	$depDAO = new DepartmentDAO();
	$departments=$depDAO->getAllDepartments();
	require_once $_SESSION["root"].'php/View/ViewRegisterProject.php';
} else if ($action == 'postCadastraProject') {
	$cFunc = new ControllerProject();
	$cFunc->setProject();
} else if ($action == 'visualizarProjeto') {
	$cFunc = new ControllerProject();
	$cFunc->getAllProjects();
} else if ($action == 'assignProject' && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
	$FuncionarioDAO = new FuncionarioDAO();
	$employees=$FuncionarioDAO->getAllFuncionarios();

	$projDAO = new ProjectDAO();
	$projects = $projDAO->getAllProjects();

	require_once $_SESSION["root"].'php/View/ViewAssignProject.php';
} else if ($action == 'postAssignProject'){
	$dao = new Func_ProjDAO();
	$dao->setRel($_SESSION["projectForm"]["emp"], $_SESSION["projectForm"]["proj"]);
	header('Location: exibeFuncionarios');
} else if ($action == 'postDeassignProject'){
	$dao = new Func_ProjDAO();
	$dao->rmvRel($_SESSION["projectForm"]["emp"], $_SESSION["projectForm"]["proj"]);
	header('Location: exibeFuncionarios');
} else if ($action == 'actionProject' && isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']){
	$_SESSION["projectForm"]["emp"] = $_POST["emp"];
	$_SESSION["projectForm"]["proj"] = $_POST["proj"];
	if($_POST["action"] == "assign")
		header("Location: postAssignProject");
	else{
		header("Location: postDeassignProject");

	}
} else {
	echo "404: Page not found";
	//isso trata todo erro 404, podemos criar uma view mais elegante para exibir o aviso ao usuário.
}

?>

