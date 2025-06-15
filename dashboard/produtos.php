<?php
include 'dashboard/config.php';
$produtos = listarPerfumes(); // cria esta função no config.php
?>

<h1>Produtos</h1>
<a href="adicionar_produto.php">+ Adicionar Produto</a>

<table>
  <tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Preço</th>
    <th>Stock</th>
    <th>Ações</th>
  </tr>
  <?php foreach ($produtos as $produto): ?>
    <tr>
      <td><?= $produto['id'] ?></td>
      <td><?= htmlspecialchars($produto['nome']) ?></td>
      <td><?= number_format($produto['preco'], 2) ?> €</td>
      <td><?= $produto['stock'] ?></td>
      <td>
        <a href="editar_produto.php?id=<?= $produto['id'] ?>">Editar</a>
        <a href="eliminar_produto.php?id=<?= $produto['id'] ?>" onclick="return confirm('Eliminar produto?')">Eliminar</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
