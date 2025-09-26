# 📚 MinhaBiblio  

**MinhaBiblio** é um sistema web para gerenciar sua biblioteca pessoal.  
Com ele, você pode cadastrar livros, acompanhar seu progresso de leitura, avaliar obras e manter organizado tudo o que já leu, está lendo ou pretende ler.  

---

## 🏷️ Badges  

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)  
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)  
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)  
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)  
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)  

---

## 🚀 Funcionalidades  

- 👤 **Cadastro de usuários**  
- 📖 **Gerenciamento de livros** (título, autor, gênero, capa, comentários)  
- 🔖 **Status de leitura** (*lido, lendo, quero ler*)  
- 📊 **Controle de progresso** (páginas lidas / total)  
- ⭐ **Avaliações** de 1 a 5 com comentários  
- 🎭 **Gêneros literários** cadastrados  
- 🔐 **Login de usuários**  

---

## 🛠️ Tecnologias Utilizadas  

- **Front-end:** HTML, CSS, JavaScript  
- **Back-end:** PHP  
- **Banco de Dados:** MySQL  

---

## 🗂️ Estrutura do Banco de Dados  

- **usuarios** → armazena dados de login e autenticação  
- **tbl_generos** → lista de gêneros literários  
- **tbl_livros** → todos os livros cadastrados (inclui status de leitura)  
- **tbl_avaliacoes** → avaliações de livros com notas e comentários  

---

## 📌 Status de Leitura  

Cada livro pode ter apenas um status:  

- ✅ **Lido** – já concluído  
- 📘 **Lendo** – em andamento, com progresso atualizado  
- 📅 **Quero ler** – planejado para o futuro  

---

## 📷 Capturas de Tela
![Tela inicial](<img width="1918" height="887" alt="Captura de tela 2025-09-26 003822" src="https://github.com/user-attachments/assets/61d0ed89-7764-44bb-80e4-a158ff398a0f" />
)
![Cadastro de livro](<img width="1918" height="887" alt="Captura de tela 2025-09-26 003908" src="https://github.com/user-attachments/assets/09e850c6-03c2-41f2-9371-7636cbaf870f" />
)
![Edição de livros](<img width="1918" height="886" alt="Captura de tela 2025-09-26 003844" src="https://github.com/user-attachments/assets/b6f908a7-46ee-4db9-a4f4-6e2c9bb60866" />
)

---

## Como executar o projeto
- Clone o repositório: git clone https://github.com/seu-usuario/minha-biblio.git  
- Importe o banco de dados db_biblio.sql no MySQL.  
- Configure a conexão no arquivo config.php (usuário, senha e nome do banco).
- Inicie um servidor local (exemplo: XAMPP).
- Acesse no navegador: http://localhost/minha-biblio


## Desenvolvido por: Evelyn Vareiro
