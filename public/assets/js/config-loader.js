/**
 * CONFIG LOADER - Carrega configurações dinâmicas do servidor
 * 
 * Uso:
 * - ConfigLoader.init() - Inicializar e carregar configurações
 * - ConfigLoader.get('site.name') - Obter uma configuração
 * - ConfigLoader.getSection('home') - Obter uma seção
 */

class ConfigLoader {
    constructor() {
        this.config = {};
        this.isLoaded = false;
        this.cache = {};
    }

    /**
     * Inicializar e carregar configurações
     */
    async init() {
        try {
            const response = await fetch('/api/v1/config');
            const data = await response.json();

            if (data.success) {
                this.config = data.data || {};
                this.isLoaded = true;
                console.log('✓ Configurações carregadas com sucesso');
                return true;
            } else {
                console.error('✗ Erro ao carregar configurações:', data.error);
                return false;
            }
        } catch (error) {
            console.error('✗ Erro de conexão ao carregar configurações:', error);
            return false;
        }
    }

    /**
     * Obter uma configuração específica
     * @param {string} path - Caminho da configuração (ex: 'site.name')
     * @param {*} defaultValue - Valor padrão se não encontrar
     */
    get(path, defaultValue = null) {
        if (!this.isLoaded) {
            console.warn('⚠ Configurações ainda não carregadas');
            return defaultValue;
        }

        const keys = path.split('.');
        let value = this.config;

        for (const key of keys) {
            if (value && typeof value === 'object' && key in value) {
                value = value[key];
            } else {
                return defaultValue;
            }
        }

        return value;
    }

    /**
     * Obter uma seção inteira
     * @param {string} section - Nome da seção
     */
    getSection(section) {
        return this.get(section, {});
    }

    /**
     * Atualizar elemento HTML com configuração
     * @param {string} selector - Seletor CSS
     * @param {string} configPath - Caminho da configuração
     * @param {string} attribute - Atributo HTML ('textContent', 'innerHTML', 'src', etc)
     */
    updateElement(selector, configPath, attribute = 'textContent') {
        const element = document.querySelector(selector);
        if (!element) {
            console.warn(`⚠ Elemento não encontrado: ${selector}`);
            return;
        }

        const value = this.get(configPath);
        if (value !== null) {
            if (attribute === 'textContent') {
                element.textContent = value;
            } else if (attribute === 'innerHTML') {
                element.innerHTML = value;
            } else {
                element.setAttribute(attribute, value);
            }
        }
    }

    /**
     * Aplicar cores de branding
     */
    applyBranding() {
        const branding = this.getSection('branding');

        const root = document.documentElement;
        root.style.setProperty('--color-primary', branding.primary_color || '#6366f1');
        root.style.setProperty('--color-secondary', branding.secondary_color || '#ffffff');
        root.style.setProperty('--color-accent', branding.accent_color || '#fbbf24');
        root.style.setProperty('--color-text', branding.text_color || '#1f2937');

        console.log('✓ Branding aplicado');
    }

    /**
     * Renderizar planos dinamicamente
     * @param {string} containerId - ID do container
     */
    renderPlans(containerId) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`⚠ Container não encontrado: ${containerId}`);
            return;
        }

        const plans = this.getSection('plans');
        if (!Array.isArray(plans) || plans.length === 0) {
            console.warn('⚠ Nenhum plano encontrado');
            return;
        }

        container.innerHTML = plans.map(plan => `
            <div class="plan-card">
                <div class="plan-header">
                    <h3>${plan.name}</h3>
                    <div class="price">R$ ${plan.price.toFixed(2)}</div>
                    <div class="items">${plan.items_count}</div>
                </div>
                <div class="plan-body">
                    <p>${plan.description}</p>
                    <ul class="features">
                        ${plan.features.map(f => `<li>✓ ${f}</li>`).join('')}
                    </ul>
                    <a href="checkout.php?plan=${plan.code}" class="btn-subscribe">Assinar ${plan.name}</a>
                </div>
            </div>
        `).join('');

        console.log(`✓ ${plans.length} planos renderizados`);
    }

    /**
     * Renderizar seção "Como Funciona"
     * @param {string} containerId - ID do container
     */
    renderHowItWorks(containerId) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`⚠ Container não encontrado: ${containerId}`);
            return;
        }

        const howItWorks = this.getSection('home.how_it_works');
        const steps = howItWorks.steps || [];

        container.innerHTML = `
            <h2>${howItWorks.title}</h2>
            <p>${howItWorks.subtitle}</p>
            <div class="steps">
                ${steps.map((step, index) => `
                    <div class="step">
                        <div class="step-number">${index + 1}</div>
                        <h3>${step.title}</h3>
                        <p>${step.description}</p>
                    </div>
                `).join('')}
            </div>
        `;

        console.log(`✓ Seção "Como Funciona" renderizada`);
    }

    /**
     * Atualizar meta tags
     */
    updateMetaTags() {
        const site = this.getSection('site');

        // Title
        document.title = site.name || 'Clubinho Pet Box';

        // Meta description
        let metaDesc = document.querySelector('meta[name="description"]');
        if (!metaDesc) {
            metaDesc = document.createElement('meta');
            metaDesc.name = 'description';
            document.head.appendChild(metaDesc);
        }
        metaDesc.content = site.description || '';

        // Favicon
        let favicon = document.querySelector('link[rel="icon"]');
        if (!favicon) {
            favicon = document.createElement('link');
            favicon.rel = 'icon';
            document.head.appendChild(favicon);
        }
        favicon.href = site.favicon || '/assets/images/logo/favicon.ico';

        console.log('✓ Meta tags atualizadas');
    }

    /**
     * Atualizar logo
     * @param {string} selector - Seletor CSS da logo
     */
    updateLogo(selector) {
        const logo = document.querySelector(selector);
        if (!logo) {
            console.warn(`⚠ Logo não encontrada: ${selector}`);
            return;
        }

        const logoUrl = this.get('site.logo');
        if (logoUrl) {
            logo.src = logoUrl;
            console.log('✓ Logo atualizada');
        }
    }

    /**
     * Atualizar informações de contato
     * @param {string} emailSelector - Seletor do email
     * @param {string} phoneSelector - Seletor do telefone
     * @param {string} whatsappSelector - Seletor do WhatsApp
     */
    updateContact(emailSelector, phoneSelector, whatsappSelector) {
        const contact = this.getSection('contact');

        if (emailSelector) {
            this.updateElement(emailSelector, 'contact.email', 'textContent');
        }
        if (phoneSelector) {
            this.updateElement(phoneSelector, 'contact.phone', 'textContent');
        }
        if (whatsappSelector) {
            const whatsappLink = document.querySelector(whatsappSelector);
            if (whatsappLink && contact.whatsapp) {
                whatsappLink.href = `https://wa.me/${contact.whatsapp}`;
            }
        }

        console.log('✓ Informações de contato atualizadas');
    }
}

// ============================================
// INSTÂNCIA GLOBAL
// ============================================

const config = new ConfigLoader();

// ============================================
// INICIALIZAR QUANDO DOM ESTIVER PRONTO
// ============================================

document.addEventListener('DOMContentLoaded', async () => {
    await config.init();
    config.applyBranding();
    config.updateMetaTags();
});
