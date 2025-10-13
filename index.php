<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Manutenção</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css"> 
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

    <div id="login">
        <div class="container-login">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Correios_logo.svg/320px-Correios_logo.svg.png" alt="Correios" width="140">
            <div class="titulo">Acesso ao Sistema de Manutenção</div>

            <select id="unidade">
                <option value="ctce-bh">MG - BELO HORIZONTE</option>
                <option value="ctce-contagem">MG - CONTAGEM</option>
                <option value="visitante">MG - VISITANTE</option>
            </select>

            <input type="text" id="matricula" placeholder="Digite sua matrícula">
            <input type="password" id="senha" placeholder="Digite sua senha">
            
            <div class="aviso" id="mensagem">Informe sua matrícula (sem traços ou pontos) e senha para acessar o sistema.</div>
            <button class="btn" onclick="validarAcesso()">Entrar</button>
        </div>
    </div>

    <div id="principal">
        <div class="top-bar">Bem-vindo ao Sistema de Manutenção</div>
        
       
        
        <button class="botao-sair" onclick="fazerLogout()">
            <i data-lucide="log-out"></i>
            Sair
        </button>
 
        <button class="botao-ajuda"data-id="ajuda" onclick="abrirAjuda()">
            <i data-lucide="circle-help"></i>
            Ajuda
        </button>


        <button class="botao-central" data-id="serviços_intranet-correios" style="top: 150px;" onclick="abrirServiçosIntranetCorreios()">
            <i data-lucide="globe"></i>Serviços - Intranet 
        </button>


        <div class="painel-botoes">
            <button class="botao-direito" data-id="manual 1" onclick="abrirManualDBCS()" title="Manual DBCS">
                <i data-lucide="book-open"></i> Manual 1
            </button>
            <button class="botao-direito" data-id="manual 2" onclick="abrirManualGO()">
                <i data-lucide="layers"></i> Manual 2
            <button class="botao-direito" data-id="manual 3" onclick="abrirManualPO()">
                <i data-lucide="box"></i> Manual 3
            </button>
            <button class="botao-direito" data-id="manual 4" onclick="abrirAlmoxarifadoSEMA()">
                <i data-lucide="archive"></i> Manual 4
            </button>

        </div>

        <div class="painel-botoes-direito">
            <button class="botao" data-id="manual 5"onclick="abrirPainelGO()">
                <i data-lucide="tv"></i> Manual 5
            </button>

            <button class="botao" data-id="manual 6"onclick="abrirPainelPO()">
                <i data-lucide="monitor"></i> Manual 6
            </button>
        </div>

        
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
    // URLs de destino unificadas
    const urlsDestino = {
        "serviços_intranet-correios": { url: 'Cole aqui o destino do arquivo', target: '_self' },
        "manual-dbcs": { url: 'Cole aqui o destino do arquivo', target: '_self' },
        "manual-go": { url: 'Cole aqui o destino do arquivo', target: '_self' },
        "manual-po": { url: 'Cole aqui o destino do arquivo', target: '_self' },
        "Painel-GO": { url: 'Cole aqui o destino do arquivo', target: '_blank' },
        "Painel-PO": { url: 'Cole aqui o destino do arquivo', target: '_blank' },
        "almoxarifado": { url: 'Cole aqui o destino do arquivo', target: '_blank' }
    };

    // Referências aos elementos do DOM
    const campoMatricula = document.getElementById('matricula');
    const selectUnidade = document.getElementById('unidade');
    const msg = document.getElementById('mensagem');
    const campoSenha = document.getElementById('senha'); 
    // NOVO: Referência à barra de topo para atualizar a saudação
    const topBar = document.querySelector('.top-bar'); 


    // Função de validação AJAX (Comunicação com o PHP)
    function validarAcesso() {
        const unidadeSelecionada = selectUnidade.value;
        const matricula = campoMatricula.value.trim();
        const senha = campoSenha.value; 

        if (matricula === "" || senha === "") {
            msg.innerHTML = "<b>Erro:</b> Preencha a matrícula e a senha para acessar.";
            msg.style.color = "red";
            return;
        }

        // Limpa mensagem de erro anterior e mostra status de verificação
        msg.style.color = "#333";
        msg.innerHTML = "Verificando dados de acesso...";

        // Chamada AJAX usando jQuery
        $.ajax({
            url: 'validar_login.php', // Arquivo PHP no servidor
            type: 'POST',
            data: {
                unidade: unidadeSelecionada,
                matricula: matricula,
                senha: senha 
            },
            dataType: 'json',
            success: function(response) {
                if (response.acesso === true) {
                    // SUCESSO: Armazena dados na sessão do navegador (sessionStorage)
                    sessionStorage.setItem("logado", "true");
                    sessionStorage.setItem("usuario", matricula);
                    // NOVO: Armazena o nome e a unidade (se o PHP retornar)
                    sessionStorage.setItem("nome_usuario", response.nome_usuario || matricula);
                    sessionStorage.setItem("unidade", response.unidade || unidadeSelecionada);
                    
                    
                    // LÓGICA DE TROCA DE SENHA
                    if (response.precisa_trocar === true) {
                        // Se for a senha padrão, redireciona para a tela de troca
                        window.location.href = 'troca_senha.php'; 
                    } else {
                        // Se a senha já foi trocada, segue para a tela principal
                        mostrarPrincipal();
                    }

                } else {
                    // FALHA: Exibe mensagem de erro do servidor
                    msg.textContent = response.mensagem;
                    msg.style.color = "red";
                }
            },
            error: function() {
                msg.textContent = "Erro de comunicação com o servidor. Verifique o XAMPP/Apache.";
                msg.style.color = "red";
            }
        });
    }

    // Atalhos do teclado (Enter e Escape)
    [campoMatricula, campoSenha].forEach(campo => {
        campo.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                validarAcesso();
            } else if (event.key === "Escape") {
                campoMatricula.value = "";
                campoSenha.value = "";
                msg.style.color = "#333";
                msg.innerHTML = "Informe sua matrícula (sem traços ou pontos) e senha para acessar o sistema.";
            }
        });
    });

    // Mostra tela inicial e esconde login
    function mostrarPrincipal() {
        const nome = sessionStorage.getItem("nome_usuario");
        const unidadeCompleta = sessionStorage.getItem("unidade");
        
        // Formata a unidade (ex: ctce-bh -> BH)
        const unidadeCurta = unidadeCompleta ? unidadeCompleta.toUpperCase().replace('CTCE-', '') : 'CTCE';
        
        // NOVO: Define a mensagem de boas-vindas com o nome e a unidade
        let saudacao = `Bem-vindo(a), ${nome || 'nome'} | Sistema de Manutenção ${unidadeCurta}`;
        
        topBar.innerHTML = saudacao; // Atualiza o texto da barra de topo

        document.getElementById("login").style.display = "none";
        document.getElementById("principal").style.display = "block";
        lucide.createIcons();
    }

    // Verifica login no carregamento da página
    window.onload = function() {
        if (sessionStorage.getItem("logado") === "true") {
            mostrarPrincipal();
        }
    }

    // FUNÇÃO PARA FAZER LOGOUT
    function fazerLogout() {
        // Limpa todos os dados da sessão do navegador
        sessionStorage.clear(); 

        // Redireciona para a página de login
        window.location.href = 'index.php';
    }

    // Funções de navegação (mapeamento do HTML)
    function navegar(idBotao) {
        const destino = urlsDestino[idBotao];
        if (destino) {
            window.open(destino.url, destino.target);
        }
    }
    function abrirServiçosIntranetCorreios() { navegar("manual 1"); }
    function abrirManualDBCS() { navegar("manual 2"); }
    function abrirManualGO() { navegar("manual 3"); }
    function abrirManualPO() { navegar("manual 4"); }
    function abrirPainelGO() { navegar("manual 5"); }
    function abrirPainelPO() { navegar("manual 6"); }
    function abrirAlmoxarifadoSEMA() { navegar("manual 7"); }

    function abrirAjuda() {
        const largura = window.innerWidth * 0.8;
        const altura = window.innerHeight * 0.8;
        const esquerda = (window.innerWidth - largura) / 2;
        const topo = (window.innerHeight - altura) / 2;

        window.open(
            'Cole aqui o destino do arquivo',
            'AjudaPopup',
            `width=${largura},height=${altura},left=${esquerda},top=${topo},
             resizable=yes,scrollbars=yes,
             toolbar=no,location=no,directories=no,status=no,menubar=no`
        );
    }
    
    // Inicializa os ícones
    lucide.createIcons();
</script>                
        
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<footer class="rodape-tecnologias">           
    <div class="rodape-conteudo">        
        <div class="icones-tecnologia"> 
            <p>Desenvolvido com as tecnologias:</p>
            <a href="#" title="HTML5"><i class="fa-brands fa-html5 html-icon"></i></a>
            <a href="#" title="CSS3"><i class="fa-brands fa-css3-alt css-icon"></i></a>
            <a href="#" title="JavaScript"><i class="fa-brands fa-js-square js-icon"></i></a>
            <a href="#" title="SQL/Database"><i class="fa-solid fa-database sql-icon"></i></a>
        </div>
        <p><&copy; <?php echo date("Y"); ?>Site SEMA - Desenvolvido Luiz</p>
       
    </div>
  
</footer>
</body>
</html>