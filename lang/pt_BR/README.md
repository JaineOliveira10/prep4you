# Traduções - Alteração de Senha

## Arquivos Criados/Modificados

### 1. Arquivos de Tradução
- `passwords.php` - Mensagens específicas para senhas
- `messages.php` - Mensagens gerais da aplicação  
- `ui.php` - Elementos da interface do usuário
- `validation.php` - Regras de validação (atualizado)

### 2. Request Personalizado
- `UpdatePasswordRequest.php` - Validação com mensagens traduzidas

### 3. Componente Blade
- `form-errors.blade.php` - Exibição consistente de erros

### 4. Middleware
- `SetLocale.php` - Garantir idioma correto

## Como Usar

### Mensagens de Senha
```php
__('passwords.current_incorrect')
__('passwords.updated_successfully')
__('passwords.min_length', ['min' => 6])
```

### Mensagens de Interface
```php
__('ui.buttons.change_password')
__('ui.labels.current_password')
__('ui.placeholders.enter_current_password')
```

### Validação
```php
__('validation.attributes.password')
__('validation.confirmed')
```

## Estrutura das Traduções

### passwords.php
- Mensagens de reset de senha
- Mensagens customizadas para alteração
- Validações específicas de senha

### validation.php  
- Regras de validação traduzidas
- Atributos dos campos
- Mensagens de erro padrão

### ui.php
- Botões e labels
- Placeholders
- Títulos e instruções

### messages.php
- Mensagens gerais do sistema
- Mensagens de sucesso/erro
- Mensagens de autorização