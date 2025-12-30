### Gerenciamento de Remessas

<br>

Esta funcionalidade consiste no gerenciamento da preparação e da coleta dos produtos.

As remessas podem ser filtradas por cliente, CD, status, data da remessa ou data da coleta, facilitando o controle e a organização das operações. 

<img
  src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/Imagens/Gerenciamento%20de%20Envios%201.png"
  alt="Gerenciamento de Envios"
  style="max-width: 100%; height: auto;"
/>

Na listagem são exibidas todas as remessas geradas pelo cliente, o filtro inicial lista as remessas com coleta para data atual + 7 dias.

<img
  src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/Imagens/Gerenciamento%20de%20Envios%202.png"
  alt="Gerenciamento de Envios"
  style="max-width: 100%; height: auto;"
/>

#### Status da Remessa

<br>

A remessa passa por diferentes status na plataforma ao longo de todo o processo, desde o lançamento, passando pela preparação, coleta, fechamento mensal e pagamento da fatura.

Abaixo estão os status possíveis de uma remessa e seus respectivos significados:

- **Pendente:** status inicial após o lançamento da remessa. Indica que ela está na fila para início da preparação dos produtos.

- **Em Preparação:** indica que a preparação e a etiquetagem dos produtos foram iniciadas.

- **Possui Pendências:** indica que a remessa possui alguma inconsistência ou informação pendente que precisa ser ajustada antes de avançar para a próxima etapa.

- **Embalado:** indica que a preparação e a etiquetagem dos produtos foram finalizadas.

- **Coletado:** indica que a remessa foi coletada pela transportadora.

- **Fatura Gerada:** indica que a remessa faz parte de um fechamento mensal e está aguardando o pagamento por parte do cliente.

- **Pago:** indica que a remessa teve sua fatura paga pelo cliente.

#### Preparação dos Produtos

<br>

Para iniciar a preparação dos produtos, o Prep Center deverá baixar os PDFs necessários e gerenciar os status da remessa conforme o fluxo operacional. 

##### Baixar PDFs e Ordem de Preparação

<br>

Os PDFs informados pelo cliente no lançamento da remessa: etiquetas individuais, etiquetas master e nota fiscal, devem ser baixados pelo Prep Center.

Além disso, é necessário baixar o PDF da ordem de preparação, que contém a lista completa dos produtos que deverão ser preparados e embalados.

<video controls width="720">
  <source src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/V%C3%ADdeos/Ordem%20de%20Prepara%C3%A7%C3%A3o.mp4" type="video/mp4">
</video>

<br>
Abaixo segue um vídeo explicativo demonstrando como realizar o download de todos os PDFs da remessa. 
<br>

<video controls width="720">
  <source src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/V%C3%ADdeos/Download%20dos%20PDFs..mp4" type="video/mp4">
</video>
<br>

##### Fluxo Operacional de Status

<br>

O fluxo abaixo apresenta todos os status da remessa e o relacionamento entre eles ao longo do processo. 

<img
  class="manual-image"
  src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/Imagens/Fluxo%20de%20Status.png"
  alt="Fluxo de Status"
  style="max-width: 100%; height: auto;"
/>

<br>

- **Pendente:** pode ser alterado para “Em Preparação”.

- **Em Preparação:** pode ser alterado para “Embalado”. Caso tenha sido colocado em preparação por engano, o status pode ser retornado para “Pendente”.
Também é possível alterar para “Possui Pendências” quando houver impedimentos para a continuidade da preparação. 

- **Possui Pendências:** pode ser alterado novamente para “Em Preparação” após a resolução da pendência junto ao cliente. 

- **Embalado:** pode ser alterado para “Coletado”, momento em que deverá ser informado o comprovante de coleta quando a transportadora realizar a coleta. Também pode ser retornado para “Em Preparação” caso o status tenha sido alterado incorretamente. 

- **Coletado:** pode ser alterado para “Embalado” caso o status tenha sido definido incorretamente. 

Os status “Fatura Gerada” e “Pago” são gerenciados exclusivamente no fechamento mensal, não sendo possível defini-los manualmente na tela de gerenciamento de remessas. 

<video controls width="720">
  <source src="https://pub-f968f26c5ae542c2a6c19b82d190b8fb.r2.dev/V%C3%ADdeos/Troca%20de%20Status%20da%20Remessa.mp4" type="video/mp4">
</video>
