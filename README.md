# shopping-cart

# Tipos de Desconto:

1. Desconto de porcentagem com base no valor total

Oferecemos 15% de desconto para carrinhos a partir de R$3000,00.

2. Desconto de quantidade do mesmo item

A cada duas unidades compradas de certos produtos, a terceira unidade será gratuita, ou seja leve 3, pague 2. Isso vale para múltiplos também. Levando 9 unidades por exemplo, o cliente pagará somente 6 unidades. Os produtos que participam dessa promoção podem ser consultados através da config api.php.

3. Desconto de porcentagem no item mais barato de uma mesma categoria

Ao comprar dois ou mais produtos diferentes de uma determinada categoria, somente uma unidade do produto mais barato dessa categoria deve receber 40% de desconto. As categorias determinadas podem ser consultadas através da config api.php.

4. Desconto de porcentagem para colaboradores

Um usuário que seja colaborador tem 20% de desconto no total do carrinho.

5. Desconto em valor para novos usuários

Caso seja a primeira compra, o usuário tem um desconto fixo de R$25,00 em compras acima de R$50,00. A rota /user/{email} retorna 404 caso o usuário não exista e se ele não existe ele é considerado um novo usuário.
