/**
 * Dynamic Loader - Carrega configurações e dados dinâmicos do servidor
 * 
 * Características:
 * - Carrega configurações da API
 * - Carrega planos da API
 * - Atualiza DOM com dados dinâmicos
 * - Cache local
 * - Fallback para valores padrão
 * 
 * @author Clubinho Pet Box
 * @version 1.0
 */

class DynamicLoader {
    constructor(options = {}) {
        this.apiBase = options.apiBase || '/api/v1';
        this.cacheKey = 'clubinho_config_cache';
        this.cacheDuration = options.cacheDuration || 3600000; // 1 hora
        this.config = {};
        this.plans = [];
        this.initialized = false;
    }
    
    /**
     * Inicializar loader
     */
    async init() {
        try {
            // Carregar configurações
            await this.loadConfig();
            
            // Carregar planos
            await this.loadPlans();
            
            // Aplicar configurações ao DOM
            this.applyConfig();
            
            // Aplicar planos ao DOM
            this.applyPlans();
            
            this.initialized = true;
            console.log('✓ DynamicLoader initialized successfully');
        } catch (error) {
            console.error('✗ DynamicLoader initialization failed:', error);
        }
    }
    
    /**
     * Carregar configurações da API
     */
    async loadConfig() {
        try {
            // Verificar cache
            const cached = this.getFromCache('config');
            if (cached) {
                this.config = cached;
                console.log('✓ Config loaded from cache');
                return;
            }
            
            // Buscar da API
            const response = await fetch(`${this.apiBase}/config`);
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.config = data.data || {};
                this.saveToCache('config', this.config);
                console.log('✓ Config loaded from API');
            }
        } catch (error) {
            console.warn('⚠ Failed to load config from API:', error);
            // Usar valores padrão
            this.config = this.getDefaultConfig();
        }
    }
    
    /**
     * Carregar planos da API
     */
    async loadPlans() {
        try {
            // Verificar cache
            const cached = this.getFromCache('plans');
            if (cached) {
                this.plans = cached;
                console.log('✓ Plans loaded from cache');
                return;
            }
            
            // Buscar da API
            const response = await fetch(`${this.apiBase}/plans`);
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.plans = data.data || [];
                this.saveToCache('plans', this.plans);
                console.log('✓ Plans loaded from API');
            }
        } catch (error) {
            console.warn('⚠ Failed to load plans from API:', error);
            // Usar valores padrão
            this.plans = this.getDefaultPlans();
        }
    }
    
    /**
     * Aplicar configurações ao DOM
     */
    applyConfig() {
        const config = this.config;
        
        // Atualizar título
        if (config.site_name) {
            document.title = config.site_name + ' | ' + (document.title.split('|')[1] || '');
        }
        
        // Atualizar meta description
        const metaDesc = document.querySelector('meta[name="description"]');
        if (metaDesc && config.site_description) {
            metaDesc.setAttribute('content', config.site_description);
        }
        
        // Atualizar logo
        const logos = document.querySelectorAll('img[alt*="logo"], img[alt*="Logo"], .logo img');
        if (config.site_logo_url) {
            logos.forEach(logo => {
                logo.src = config.site_logo_url;
            });
        }
        
        // Atualizar cores CSS
        if (config.site_primary_color) {
            document.documentElement.style.setProperty('--color-primary', config.site_primary_color);
        }
        if (config.site_secondary_color) {
            document.documentElement.style.setProperty('--color-secondary', config.site_secondary_color);
        }
        if (config.site_accent_color) {
            document.documentElement.style.setProperty('--color-accent', config.site_accent_color);
        }
        
        // Atualizar textos
        this.updateElementText('[data-config="site_name"]', config.site_name);
        this.updateElementText('[data-config="site_description"]', config.site_description);
        this.updateElementText('[data-config="contact_email"]', config.contact_email);
        this.updateElementText('[data-config="contact_phone"]', config.contact_phone);
        this.updateElementText('[data-config="contact_whatsapp"]', config.contact_whatsapp);
        this.updateElementText('[data-config="home_hero_title"]', config.home_hero_title);
        this.updateElementText('[data-config="home_hero_description"]', config.home_hero_description);
        this.updateElementText('[data-config="home_how_it_works_title"]', config.home_how_it_works_title);
        this.updateElementText('[data-config="about_title"]', config.about_title);
        this.updateElementText('[data-config="about_description"]', config.about_description);
        this.updateElementText('[data-config="footer_copyright"]', config.footer_copyright);
        
        // Atualizar links
        this.updateElementHref('[data-config-href="contact_email"]', `mailto:${config.contact_email}`);
        this.updateElementHref('[data-config-href="contact_whatsapp"]', `https://wa.me/${config.contact_whatsapp?.replace(/\D/g, '')}`);
    }
    
    /**
     * Aplicar planos ao DOM
     */
    applyPlans() {
        const plansContainer = document.querySelector('[data-plans-container]');
        
        if (!plansContainer || this.plans.length === 0) {
            return;
        }
        
        // Limpar container
        plansContainer.innerHTML = '';
        
        // Adicionar cada plano
        this.plans.forEach(plan => {
            const planElement = this.createPlanElement(plan);
            plansContainer.appendChild(planElement);
        });
    }
    
    /**
     * Criar elemento de plano
     */
    createPlanElement(plan) {
        const div = document.createElement('div');
        div.className = 'plan-card';
        div.innerHTML = `
            <div class="plan-header">
                <h3>${plan.name}</h3>
                ${plan.is_popular ? '<span class="badge-popular">Popular</span>' : ''}
            </div>
            <div class="plan-price">
                <span class="price">${this.formatPrice(plan.price)}</span>
                <span class="currency">${plan.currency}/mês</span>
            </div>
            <p class="plan-description">${plan.description || ''}</p>
            <ul class="plan-features">
                ${(plan.features || []).map(feature => `<li>${feature}</li>`).join('')}
            </ul>
            <a href="${plan.checkout_url}" class="btn btn-primary">Assinar Agora</a>
        `;
        return div;
    }
    
    /**
     * Atualizar texto de elemento
     */
    updateElementText(selector, text) {
        const elements = document.querySelectorAll(selector);
        if (text) {
            elements.forEach(el => {
                el.textContent = text;
            });
        }
    }
    
    /**
     * Atualizar href de elemento
     */
    updateElementHref(selector, href) {
        const elements = document.querySelectorAll(selector);
        if (href) {
            elements.forEach(el => {
                el.href = href;
            });
        }
    }
    
    /**
     * Formatar preço
     */
    formatPrice(price) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(price);
    }
    
    /**
     * Obter configuração padrão
     */
    getDefaultConfig() {
        return {
            site_name: 'Clubinho Pet Box',
            site_description: 'Assinatura premium de produtos selecionados para o seu pet',
            site_logo_url: 'assets/images/logo/logo.jpg',
            site_primary_color: '#000000',
            site_secondary_color: '#ffffff',
            site_accent_color: '#d4af37',
            contact_email: 'email-contato@clubinhopetbox.com.br',
            contact_phone: '(11) 99999-9999',
            contact_whatsapp: '(11) 99999-9999',
            home_hero_title: 'Momentos especiais merecem produtos especiais',
            home_hero_description: 'Uma seleção exclusiva de produtos premium entregue mensalmente na sua casa.',
            home_how_it_works_title: 'Como funciona',
            about_title: 'Sobre Clubinho Pet Box',
            about_description: 'Clubinho Pet Box nasceu da paixão por pets...',
            footer_copyright: '© 2024 Clubinho Pet Box. Todos os direitos reservados.'
        };
    }
    
    /**
     * Obter planos padrão
     */
    getDefaultPlans() {
        return [
            {
                id: 1,
                name: 'Clubinho Pet Happy',
                slug: 'happy',
                description: 'Box essencial com produtos selecionados',
                price: 101.47,
                currency: 'BRL',
                checkout_url: 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
                features: ['4 a 6 produtos essenciais', 'Brinquedos divertidos', 'Petiscos saborosos'],
                is_active: true,
                is_popular: false
            },
            {
                id: 2,
                name: 'Clubinho Pet Love',
                slug: 'love',
                description: 'Box premium com produtos exclusivos',
                price: 164.97,
                currency: 'BRL',
                checkout_url: 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
                features: ['6 a 8 produtos exclusivos', 'Brinquedos interativos', 'Petiscos premium'],
                is_active: true,
                is_popular: true
            },
            {
                id: 3,
                name: 'Clubinho Pet Deluxe',
                slug: 'deluxe',
                description: 'Box super premium com o que há de melhor',
                price: 253.87,
                currency: 'BRL',
                checkout_url: 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322',
                features: ['8 a 10 produtos de luxo', 'Brinquedos importados', 'Petiscos gourmet'],
                is_active: true,
                is_popular: false
            }
        ];
    }
    
    /**
     * Salvar no cache local
     */
    saveToCache(key, data) {
        try {
            const cache = {
                data: data,
                timestamp: Date.now()
            };
            localStorage.setItem(`${this.cacheKey}_${key}`, JSON.stringify(cache));
        } catch (error) {
            console.warn('Failed to save to cache:', error);
        }
    }
    
    /**
     * Obter do cache local
     */
    getFromCache(key) {
        try {
            const cached = localStorage.getItem(`${this.cacheKey}_${key}`);
            if (!cached) return null;
            
            const { data, timestamp } = JSON.parse(cached);
            
            // Verificar se cache expirou
            if (Date.now() - timestamp > this.cacheDuration) {
                localStorage.removeItem(`${this.cacheKey}_${key}`);
                return null;
            }
            
            return data;
        } catch (error) {
            console.warn('Failed to get from cache:', error);
            return null;
        }
    }
    
    /**
     * Limpar cache
     */
    clearCache() {
        try {
            localStorage.removeItem(`${this.cacheKey}_config`);
            localStorage.removeItem(`${this.cacheKey}_plans`);
            console.log('✓ Cache cleared');
        } catch (error) {
            console.warn('Failed to clear cache:', error);
        }
    }
}

// Inicializar ao carregar página
document.addEventListener('DOMContentLoaded', () => {
    const loader = new DynamicLoader();
    loader.init();
});
