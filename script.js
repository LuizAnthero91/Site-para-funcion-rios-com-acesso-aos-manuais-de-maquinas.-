// ... (dentro de <script>)

    const campoMatricula = document.getElementById('matricula');
    const selectUnidade = document.getElementById('unidade');
    const msg = document.getElementById('mensagem');
    const campoSenha = document.getElementById('senha'); // NOVO: Captura o campo Senha


// ...
// Função de validação AJAX (Comunicação com o PHP)
function validarAcesso() {
    const unidadeSelecionada = selectUnidade.value;
    const matricula = campoMatricula.value.trim();
    const senha = campoSenha.value; 

    if (matricula === "" || senha === "") {
        msg.innerHTML = " **Erro:** Preencha a matrícula e a senha para acessar.";
        msg.style.color = "red";
        return;
    }

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
                // SUCESSO: Armazena dados na sessão
                sessionStorage.setItem("logado", "true");
                sessionStorage.setItem("usuario", matricula);
                sessionStorage.setItem("unidade", unidadeSelecionada);
                
                // **NOVA LÓGICA DE TROCA DE SENHA**
                if (response.precisa_trocar === true) {
                    // Se for a senha padrão, redireciona para a tela de troca
                    window.location.href = 'trocar_senha.php'; 
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
// ... (o restante do script permanece igual)

      // Limpa mensagem de erro anterior
      msg.style.color = "#333";
      msg.innerHTML = "Verificando dados de acesso...";

      // Chamada AJAX para o PHP
      $.ajax({
          url: 'validar_login.php', 
          type: 'POST',
          data: {
              unidade: unidadeSelecionada,
              matricula: matricula,
              senha: senha // NOVO: Envia a senha para o PHP
          },
// ... (o restante da função permanece igual)