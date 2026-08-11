-- 013: Expand existing blog posts + seed 8 roadmap articles
-- Idempotent: UPDATEs by slug; INSERTs guarded by NOT EXISTS.

UPDATE `blog_posts` SET
  `excerpt` = 'Uma curadoria mensal tira o peso de escolher o próximo brinquedo ou petisco — e transforma o cuidado em ritual.',
  `body` = '<p>Ter um cão é alegria — e também uma rotina de compras: brinquedos que perdem o graça, petiscos que acabam no meio da semana e itens de higiene que você sempre esquece no mercado.</p>
<p>Uma <strong>box de assinatura</strong> para cães resolve parte disso com curadoria: a cada mês chega uma seleção pensada para o perfil do pet. Você ganha previsibilidade sem abrir mão da surpresa.</p>
<h2>Ritual, não só entrega</h2>
<p>O melhor da box não é só o produto. É o momento de abrir juntos, observar o que o cão escolhe primeiro e calibrar o ciclo seguinte (mastigação, calma, brincadeira, farejar).</p>
<h3>O que costuma entrar numa boa curadoria</h3>
<ul>
<li>Brinquedo ou desafio mental alinhado ao porte e ao temperamento</li>
<li>Petisco especial em porção consciente</li>
<li>Item de conforto ou higiene para o cuidado do dia a dia</li>
<li>Pequeno “extra” que deixa a abertura memorável</li>
</ul>
<h2>Para quem faz sentido assinar</h2>
<p>Assinatura brilha quando você quer reduzir decisão, manter novidade na rotina e presentear o dog com constância — sem acumular compras por impulso.</p>
<h2>Happy, Love ou Deluxe</h2>
<p>No Clubinho Pet Box montamos três caminhos. Se ainda não sabe qual escolher, o <a href="/quiz">quiz</a> indica o ponto de partida; depois você pode comparar no <a href="/planos">página de planos</a>.</p>
<p>Assinar deixa o cuidado mais leve — e o vínculo, mais presente.</p>',
  `seo_title` = 'Box mensal para cães | Clubinho Pet Box',
  `seo_description` = 'Entenda como uma assinatura de box mensal melhora a rotina com seu cão e vira um ritual de carinho.',
  `status` = 'published'
WHERE `slug` = 'assinatura-box-mensal-caes';

UPDATE `blog_posts` SET
  `excerpt` = 'Nem todo petisco “gourmet” é adequado. Veja sinais simples de qualidade e exageros a evitar.',
  `body` = '<p>Petiscos são ferramentas de vínculo e treino — mas também de calorias e ingredientes. Antes de celebrar o que veio na box (ou de colocar no carrinho), vale ler o rótulo com calma.</p>
<h2>Sinais de cuidado no rótulo</h2>
<ul>
<li>Composição clara, com proteína no início da lista</li>
<li>Poucos aditivos desnecessários e nomes que você reconhece</li>
<li>Tamanho e textura adequados ao porte e à idade do cão</li>
<li>Indicação de uso e validade visíveis</li>
</ul>
<h2>Quanto e com que frequência</h2>
<p>Use petiscos como recompensa pontual. Uma regra prática: trate petiscos como uma fatia pequena do “orçamento calórico” do dia. Se o cão engorda fácil, prefira pedacinhos menores ou reduza a ração proporcionalmente (com orientação do veterinário).</p>
<h3>Erros comuns</h3>
<ul>
<li>Dar petisco humano gordo ou temperado “só um pouquinho”</li>
<li>Deixar o dog associar latido a recompensa automática</li>
<li>Trocar refeição por pilha de snacks</li>
</ul>
<h2>Na curadoria Clubinho</h2>
<p>Buscamos equilíbrio entre sabor e cuidado — especialmente para tutores que querem qualidade sem complicar a rotina. Quer calibrar o perfil do seu cão? Faça o <a href="/quiz">quiz</a> ou veja os <a href="/planos">planos</a>.</p>',
  `seo_title` = 'Petiscos saudáveis para cães',
  `seo_description` = 'Como escolher petiscos melhores para o seu cão: rótulo, frequência e boas práticas.',
  `status` = 'published'
WHERE `slug` = 'petiscos-saudaveis-caes';

UPDATE `blog_posts` SET
  `excerpt` = 'Latidos, destruição e agitação na saída podem ser pedido de rotina — não birra.',
  `body` = '<p>Muitos cães sofrem quando o tutor sai. Não é “manha”: é desconforto real. Reconhecer cedo ajuda a montar rituais mais suaves — e evita que o problema escale.</p>
<h2>Sinais comuns</h2>
<ul>
<li>Latidos, choro ou uivo logo após a porta fechar</li>
<li>Destruição perto da saída ou em itens do tutor</li>
<li>Agitação extrema nas despedidas (e euforia desarrazoada na volta)</li>
<li>Babação, lambedura excessiva ou recusa de comida só na ausência</li>
</ul>
<h2>O que costuma ajudar</h2>
<p>Saídas curtas e progressivas, enriquecimento ambiental (bruxquedo seguro, farejar, comedouro lento) e rotinas previsíveis reduzem o pico de estresse. Produtos de conforto podem complementar o plano — mas nunca substituem avaliação profissional em casos graves.</p>
<h3>Ritual de saída mais leve</h3>
<ol>
<li>Prepare o ambiente (água, brinquedo, farejar) antes de pegar as chaves</li>
<li>Despedida curta e neutra — sem drama prolongado</li>
<li>Volte em janelas curtas no começo e aumente aos poucos</li>
<li>Recompense calma, não a explosão de ansiedade</li>
</ol>
<h2>Quando buscar ajuda</h2>
<p>Se houver automutilação, reclamações de vizinhos constantes ou piora rápida, fale com veterinário comportamentalista. Enquanto isso, explorar mais conforto na rotina — por exemplo via <a href="/planos">plano Love</a> — e o <a href="/quiz">quiz</a> pode ajudar a alinhar a box ao perfil do dog.</p>',
  `seo_title` = 'Ansiedade de separação em cães',
  `seo_description` = 'Sinais de ansiedade de separação e rituais práticos para deixar o cão mais seguro.',
  `status` = 'published'
WHERE `slug` = 'ansiedade-separacao-caes';

UPDATE `blog_posts` SET
  `excerpt` = 'Datas especiais pedem intenção: conforto, cheiro, brincadeira ou cuidado.',
  `body` = '<p>Aniversário do dog, Dia dos Pets, volta de uma viagem — qualquer data pode ser motivo de carinho. O truque é escolher algo alinhado ao jeito do cão, não só ao que está em promoção.</p>
<h2>Quatro direções de presente</h2>
<ul>
<li><strong>Brincadeira:</strong> texturas novas e desafios mentais</li>
<li><strong>Conforto:</strong> itens macios, cheiro familiar, cantinho acolhedor</li>
<li><strong>Petiscos:</strong> recompensas especiais em porções conscientes</li>
<li><strong>Cuidado:</strong> higiene e bem-estar no dia a dia</li>
</ul>
<h3>Como não errar</h3>
<p>Observe porte, força de mordida, interesses (farejar × puxar × mastigar) e restrições alimentares. Presente bom é aquele que o cão usa — e que cabe na sua casa.</p>
<h2>Box como presente</h2>
<p>Uma box resolve a dúvida do “o que dar” com curadoria e ainda traz a surpresa da abertura juntos. Ideal para quem quer presentear sem acumular coisas aleatórias.</p>
<p>Compare <a href="/planos">Happy, Love e Deluxe</a> ou deixe o <a href="/quiz">quiz</a> sugerir o caminho.</p>',
  `seo_title` = 'Presentes para cães com intenção',
  `seo_description` = 'Ideias de presentes para cães além do brinquedo comum — conforto, cuidado e surpresa.',
  `status` = 'published'
WHERE `slug` = 'presentes-para-caes';

UPDATE `blog_posts` SET
  `excerpt` = 'Banho, orelhas e pelos: uma rotina leve protege a pele e o bem-estar.',
  `body` = '<p>Higiene boa não precisa ser ritual diário complexo. O essencial, feito com constância, já muda a pele, o cheiro e o conforto do cão.</p>
<h2>Checklist leve</h2>
<ul>
<li>Banho na frequência indicada pelo tipo de pelagem e rotina</li>
<li>Secagem completa em dobras, axilas e entre dedos</li>
<li>Atenção a orelhas (cheiro, vermelhidão, coceira) e unhas</li>
<li>Produtos adequados à pele do dog — evite improvisar com cosmético humano</li>
</ul>
<h3>Sinais de que algo está fora</h3>
<ul>
<li>Coceira constante, oleosidade excessiva ou odor forte persistente</li>
<li>Vermelhidão em orelhas ou entre dedos</li>
<li>Pelos quebradiços após banhos muito frequentes</li>
</ul>
<h2>Cuidado sem exagero</h2>
<p>Mais banho nem sempre é melhor. Respeite a pele: se houver dúvida, o veterinário orienta frequência e produtos. Itens de higiene bem escolhidos evitam irritações.</p>
<p>Na assinatura Clubinho, a curadoria também olha para esse lado do cuidado. Explore os <a href="/planos">planos</a> ou descubra o encaixe no <a href="/quiz">quiz</a>.</p>',
  `seo_title` = 'Higiene básica do cão',
  `seo_description` = 'Rotina essencial de higiene para cães: banho, orelhas e produtos sem exagero.',
  `status` = 'published'
WHERE `slug` = 'higiene-basica-caes';

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'enriquecimento-ambiental-apartamento', 'Enriquecimento ambiental para cães em apartamento', 'Espaço pequeno não precisa ser vida monótona: farejar, mastigar e desafios curtos mudam o humor do dog.', '<p>Apartamento não é sentença de tédio. Cães precisam gastar corpo <em>e</em> mente — e o enriquecimento ambiental é o atalho mais gentil quando o parque fica longe ou o dia chove.</p>
<h2>O que é enriquecimento (de verdade)</h2>
<p>É oferecer escolhas, cheiros, texturas e problemas simples para o cão resolver. Não é só “mais brinquedo no chão”: é variar o que ele fareja, mastiga e investiga ao longo da semana.</p>
<h3>Ideias que cabem em poucos metros</h3>
<ul>
<li>Esconder petiscos em toalha enrolada ou caixa de papelão</li>
<li>Rodízio de brinquedos (guarde e reapresente)</li>
<li>Tapete de farejar ou garrafa pet com furos</li>
<li>Sessões curtas de treino (5 minutos, várias vezes)</li>
</ul>
<h2>Sinais de que está funcionando</h2>
<p>Menos insistência por atenção, sono mais profundo depois das sessões e menos “bagunça de tédio” quando você trabalha.</p>
<p>Brinquedos e desafios mentais costumam aparecer nas boxes Clubinho — o <a href="/quiz">quiz</a> ajuda a calibrar o perfil e os <a href="/planos">planos</a> mostram os caminhos Happy, Love e Deluxe.</p>', '/assets/images/mood/q01.jpg', 'published', '2026-03-10 10:00:00', 'Enriquecimento ambiental para cães em apartamento', 'Ideias práticas de enriquecimento ambiental para cães que vivem em apartamento — mente cansada, casa mais calma.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'enriquecimento-ambiental-apartamento');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'ensinar-cao-ficar-sozinho', 'Como ensinar o cão a ficar sozinho aos poucos', 'Independência se treina em doses curtas: ambiente seguro, saídas progressivas e reforço de calma.', '<p>Ficar sozinho é habilidade — não testamento de amor. Quanto mais cedo (e com mais calma) você treinar, menor a chance de ansiedade de separação instalar-se.</p>
<h2>Antes de sair de verdade</h2>
<ul>
<li>Ambiente seguro: água, local confortável, brinquedo permitido</li>
<li>Ensaios com você em outro cômodo, porta aberta</li>
<li>Depois, porta fechada por segundos — e retorno sem festa exagerada</li>
</ul>
<h2>Progressão simples</h2>
<ol>
<li>Segundos → minutos curtos</li>
<li>Saia só até o elevador / corredor</li>
<li>Aumente o tempo só quando o cão estiver neutro na volta</li>
<li>Alterne durações (não só “sempre mais longo”)</li>
</ol>
<h3>O que evitar</h3>
<p>Despedidas longas e emocionadas, castigo na volta e saltos bruscos de tempo. Se houver destruição intensa ou automutilação, pause e busque ajuda profissional.</p>
<p>Quer alinhar a rotina com conforto e farejar? Veja os <a href="/planos">planos</a> ou comece pelo <a href="/quiz">quiz</a>.</p>', '/assets/images/mood/q02.jpg', 'published', '2026-03-12 10:00:00', 'Ensinar o cão a ficar sozinho', 'Passo a passo gentil para ensinar o cão a ficar sozinho sem drama — progressão, ambiente e sinais de alerta.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'ensinar-cao-ficar-sozinho');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'quanto-petisco-por-dia', 'Quanto petisco por dia sem exagero', 'Petisco é ferramenta — não segunda refeição. Uma regra prática de porção e quando cortar.', '<p>Petisco demais engorda, petisco de menos frustra o treino. O meio-termo começa com uma pergunta: <strong>quanto da ração diária estou substituindo?</strong></p>
<h2>Regra prática</h2>
<p>Muitos tutores usam a faixa de cerca de <strong>10%</strong> das calorias diárias em petiscos (ajuste com o veterinário). Prefira pedaços pequenos: o cão conta eventos de recompensa, não o tamanho do pedaço.</p>
<h3>Quando reduzir</h3>
<ul>
<li>Ganho de peso ou falta de cintura visível</li>
<li>Recusa da ração principal</li>
<li>Problemas GI após snacks gordurosos</li>
</ul>
<h2>Treino inteligente</h2>
<p>Separe uma porção da ração do dia para usar como reforço. Assim você treina sem “empurrar” calorias extras.</p>
<p>Na curadoria Clubinho, petiscos entram com intenção — descubra o encaixe no <a href="/quiz">quiz</a> ou compare <a href="/planos">planos</a>.</p>', '/assets/images/mood/q03.jpg', 'published', '2026-03-14 10:00:00', 'Quantidade de petisco por dia para cães', 'Guia prático de quanto petisco oferecer ao cão por dia sem exagerar calorias nem estragar o treino.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'quanto-petisco-por-dia');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'box-caes-pequenos', 'Box para cães pequenos: o que muda na curadoria', 'Porte pequeno pede tamanho de petisco, segurança de brinquedo e proporção — não só “versão miniatura”.', '<p>Cão pequeno não é “adulto em escala menor” para todo produto. Segurança, tamanho de petisco e peso do brinquedo mudam a curadoria.</p>
<h2>O que precisa ser diferente</h2>
<ul>
<li><strong>Petiscos:</strong> pedaços que não engasguem; mastigáveis compatíveis</li>
<li><strong>Brinquedos:</strong> sem peças que desprendam fácil; tamanho seguro</li>
<li><strong>Proporção:</strong> menos volume, mais adequação</li>
</ul>
<h3>Perguntas úteis no quiz</h3>
<p>Ambiente (apto x casa), força de mordida e preferências (farejar, puxar, mordiscar) pesam tanto quanto o porte.</p>
<h2>Qual plano olhar primeiro</h2>
<p>Happy costuma ser um ponto de partida leve; Love e Deluxe ampliam a experiência. Compare em <a href="/planos">planos</a> ou deixe o <a href="/quiz">quiz</a> sugerir.</p>', '/assets/images/mood/q04.jpg', 'published', '2026-03-16 10:00:00', 'Box para cães pequenos', 'O que muda numa box para cães pequenos: tamanho, segurança, petiscos e como escolher o plano certo.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'box-caes-pequenos');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'dia-dos-pets-checklist', 'Dia dos Pets: checklist de carinho', 'Uma data, várias formas de cuidado: do passeio especial ao ritual de unboxing em família.', '<p>O Dia dos Pets é desculpa oficial para reforçar o vínculo — sem precisar gastar fortuna ou estressar o cão.</p>
<h2>Checklist do dia</h2>
<ul>
<li>Passeio um pouco mais longo ou em rota nova (com segurança)</li>
<li>Sessão curta de brincadeira ou farejar</li>
<li>Foto ou vídeo para o álbum da família</li>
<li>Um mimo consciente (petisco especial ou item de conforto)</li>
<li>Check rápido de coleira, tag e água</li>
</ul>
<h3>Presente com intenção</h3>
<p>Prefira algo alinhado ao jeito do dog. Uma box resolve a dúvida e vira ritual de abertura — veja <a href="/planos">planos</a> ou o <a href="/quiz">quiz</a>.</p>', '/assets/images/mood/q10.jpg', 'published', '2026-03-18 10:00:00', 'Dia dos Pets: checklist de carinho', 'Checklist prático para celebrar o Dia dos Pets com o seu cão — carinho, segurança e ideias de presente.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'dia-dos-pets-checklist');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'happy-love-deluxe-diferencas', 'Happy vs Love vs Deluxe: para quem é cada um', 'Três caminhos de assinatura: do essencial ao ritual completo — sem enrolação.', '<p>Escolher plano sem clareza vira dúvida eterna. Aqui vai um mapa direto dos três caminhos Clubinho.</p>
<h2>Happy</h2>
<p>Para quem quer começar leve: surpresa mensal com curadoria essencial, ideal para testar o ritual de assinatura.</p>
<h2>Love</h2>
<p>O meio-termo mais pedido: mais variedade e presença de conforto/bem-estar, para tutores que querem a box como momento fixo do mês.</p>
<h2>Deluxe</h2>
<p>Experiência ampliada — mais itens e presença premium para quem celebra o unboxing com intensidade.</p>
<h3>Como decidir sem stress</h3>
<ol>
<li>Faça o <a href="/quiz">quiz</a> com o perfil real do cão</li>
<li>Compare preços e percepção de valor em <a href="/planos">planos</a></li>
<li>Comece; você pode evoluir de plano depois</li>
</ol>', '/assets/images/boxes/box-love.jpg', 'published', '2026-03-20 10:00:00', 'Diferenças Happy, Love e Deluxe | Clubinho', 'Compare Happy, Love e Deluxe do Clubinho Pet Box e descubra qual assinatura combina com você e seu cão.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'happy-love-deluxe-diferencas');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'como-quiz-escolhe-box', 'Como o quiz escolhe a box ideal', 'Ambiente, hábitos e preferências entram no motor — não é sorteio, é recomendação.', '<p>O quiz não é enfeite: ele traduz o jeito do seu cão em uma recomendação de ponto de partida (Happy, Love ou Deluxe).</p>
<h2>O que pedimos e por quê</h2>
<ul>
<li><strong>Ambiente e rotina:</strong> apto, casa, tempo de passeio</li>
<li><strong>Preferências:</strong> mastigar, farejar, brincar, aconchego</li>
<li><strong>Materiais e tamanho:</strong> segurança e adequação ao porte</li>
</ul>
<h2>O que acontece depois</h2>
<p>Com as respostas, o motor de regras sugere um plano. Você revisa, compara e segue para checkout — ou ajusta à vontade.</p>
<p>Pronto para tentar? Vá ao <a href="/quiz">quiz</a> ou leia os <a href="/planos">planos</a> antes.</p>', '/assets/images/mood/q01.jpg', 'published', '2026-03-22 10:00:00', 'Como o quiz escolhe a box | Clubinho', 'Entenda como o quiz do Clubinho Pet Box transforma respostas em recomendação de assinatura.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'como-quiz-escolhe-box');

INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `body`, `cover_image`, `status`, `published_at`, `seo_title`, `seo_description`)
SELECT 'ritual-unboxing-box', 'Abrindo a box: ritual de unboxing em família', 'A entrega vira memória quando todos participam — do chão limpo ao “primeiro cheiro” do dog.', '<p>Unboxing não precisa de tripés e luz de estúdio. Precisa de presença: o cão farejando, a família comentando, um ritmo calmo.</p>
<h2>Roteiro de 10 minutos</h2>
<ol>
<li>Escolha um canto seguro (longe de escadas e fios)</li>
<li>Abra a caixa sem rasgar tudo de uma vez — deixe o dog participar do cheiro</li>
<li>Apresente um item por vez; guarde o que for para depois</li>
<li>Registre uma foto ou áudio curto (só se o dog estiver confortável)</li>
<li>Encerre com um brinquedo ou petisco já liberado</li>
</ol>
<h3>Segurança</h3>
<p>Retire plásticos e lacres. Supervise mastigáveis novos. Nem todo item é “agora” — rodízio é parte da graça.</p>
<p>Quer viver isso todo mês? Compare <a href="/planos">planos</a> ou descubra o encaixe no <a href="/quiz">quiz</a>.</p>', '/assets/images/mood/q01.jpg', 'published', '2026-03-24 10:00:00', 'Ritual de unboxing da pet box', 'Como criar um ritual de unboxing da box do cão em família — simples, seguro e memorável.'
WHERE NOT EXISTS (SELECT 1 FROM `blog_posts` WHERE `slug` = 'ritual-unboxing-box');

