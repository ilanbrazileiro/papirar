# Testes recomendados

- published + URL correta => 200
- slug incorreto => 301
- draft => 404
- archived => 404
- reviewed => 404
- visitante => sem `commented_answer`
- visitante => sem indicação da correta
- POST sem login => bloqueado
- alternativa de outra questão => rejeitada
- alternativa válida autenticada => resultado + comentário
