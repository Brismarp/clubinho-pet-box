<?php

declare(strict_types=1);

namespace ClubinhoPet\Shared\Infrastructure;

use ClubinhoPet\Catalog\Application\GetPlanBySlugHandler;
use ClubinhoPet\Catalog\Application\ListPlansHandler;
use ClubinhoPet\Catalog\Application\UpdatePlanCheckoutUrlHandler;
use ClubinhoPet\Catalog\Infrastructure\Persistence\PdoPlanRepository;
use ClubinhoPet\Catalog\Infrastructure\Persistence\PdoProductRepository;
use ClubinhoPet\Analytics\Application\GetDashboardMetricsHandler;
use ClubinhoPet\Analytics\Application\IngestEventsHandler;
use ClubinhoPet\Analytics\Application\ListLeadsHandler;
use ClubinhoPet\Analytics\Application\SubmitLeadHandler;
use ClubinhoPet\Analytics\Infrastructure\Persistence\PdoAnalyticsEventRepository;
use ClubinhoPet\Analytics\Infrastructure\Persistence\PdoLeadRepository;
use ClubinhoPet\Content\Application\GetAdminPostHandler;
use ClubinhoPet\Content\Application\GetPublishedPostBySlugHandler;
use ClubinhoPet\Content\Application\HomeContentHandlers;
use ClubinhoPet\Content\Application\ListAdminPostsHandler;
use ClubinhoPet\Content\Application\ListPublishedPostsHandler;
use ClubinhoPet\Content\Application\SavePostHandler;
use ClubinhoPet\Content\Infrastructure\Persistence\PdoFaqItemRepository;
use ClubinhoPet\Content\Infrastructure\Persistence\PdoHappyMediaRepository;
use ClubinhoPet\Content\Infrastructure\Persistence\PdoPostRepository;
use ClubinhoPet\Content\Infrastructure\Persistence\PdoTestimonialRepository;
use ClubinhoPet\Fulfillment\Application\CreateBoxHandler;
use ClubinhoPet\Fulfillment\Application\ListBoxesHandler;
use ClubinhoPet\Fulfillment\Application\ShipBoxHandler;
use ClubinhoPet\Fulfillment\Infrastructure\Persistence\PdoBoxRepository;
use ClubinhoPet\Identity\Application\LoginAdminHandler;
use ClubinhoPet\Identity\Application\LogoutAdminHandler;
use ClubinhoPet\Identity\Infrastructure\PhpSessionAuth;
use ClubinhoPet\Identity\Infrastructure\PdoUserRepository;
use ClubinhoPet\Platform\Application\GetPublicSettingsHandler;
use ClubinhoPet\Platform\Application\ListSettingsHandler;
use ClubinhoPet\Platform\Application\UpdateSettingHandler;
use ClubinhoPet\Platform\Infrastructure\Persistence\PdoActivityLogRepository;
use ClubinhoPet\Platform\Infrastructure\Persistence\PdoSettingRepository;
use ClubinhoPet\Subscription\Application\ListSubscriptionsHandler;
use ClubinhoPet\Subscription\Application\RegisterInterest\RegisterInterestHandler;
use ClubinhoPet\Subscription\Application\StartCheckout\StartCheckoutHandler;
use ClubinhoPet\Subscription\Infrastructure\Payment\PlanCheckoutGateway;
use ClubinhoPet\Subscription\Infrastructure\Persistence\PdoSubscriptionRepository;
use ClubinhoPet\Support\Application\ListContactMessagesHandler;
use ClubinhoPet\Support\Application\MarkContactReadHandler;
use ClubinhoPet\Support\Application\SubmitContactMessageHandler;
use ClubinhoPet\Support\Infrastructure\Persistence\PdoContactMessageRepository;
use PDO;

final class AppContainer
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = PdoConnection::get();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function planRepository(): PdoPlanRepository
    {
        return new PdoPlanRepository($this->pdo);
    }

    public function productRepository(): PdoProductRepository
    {
        return new PdoProductRepository($this->pdo);
    }

    public function subscriptionRepository(): PdoSubscriptionRepository
    {
        return new PdoSubscriptionRepository($this->pdo);
    }

    public function userRepository(): PdoUserRepository
    {
        return new PdoUserRepository($this->pdo);
    }

    public function auth(): PhpSessionAuth
    {
        return new PhpSessionAuth();
    }

    public function settingRepository(): PdoSettingRepository
    {
        return new PdoSettingRepository($this->pdo);
    }

    public function activityLogRepository(): PdoActivityLogRepository
    {
        return new PdoActivityLogRepository($this->pdo);
    }

    public function boxRepository(): PdoBoxRepository
    {
        return new PdoBoxRepository($this->pdo);
    }

    public function contactMessageRepository(): PdoContactMessageRepository
    {
        return new PdoContactMessageRepository($this->pdo);
    }

    public function analyticsEventRepository(): PdoAnalyticsEventRepository
    {
        return new PdoAnalyticsEventRepository($this->pdo);
    }

    public function leadRepository(): PdoLeadRepository
    {
        return new PdoLeadRepository($this->pdo);
    }

    public function postRepository(): PdoPostRepository
    {
        return new PdoPostRepository($this->pdo);
    }

    public function paymentGateway(): PlanCheckoutGateway
    {
        return new PlanCheckoutGateway($this->planRepository());
    }

    public function listPlans(): ListPlansHandler
    {
        return new ListPlansHandler($this->planRepository());
    }

    public function getPlanBySlug(): GetPlanBySlugHandler
    {
        return new GetPlanBySlugHandler($this->planRepository());
    }

    public function updatePlanCheckoutUrl(): UpdatePlanCheckoutUrlHandler
    {
        return new UpdatePlanCheckoutUrlHandler($this->planRepository());
    }

    public function startCheckout(): StartCheckoutHandler
    {
        return new StartCheckoutHandler($this->planRepository(), $this->paymentGateway());
    }

    public function registerInterest(): RegisterInterestHandler
    {
        return new RegisterInterestHandler(
            $this->planRepository(),
            $this->subscriptionRepository(),
            $this->paymentGateway(),
        );
    }

    public function loginAdmin(): LoginAdminHandler
    {
        return new LoginAdminHandler($this->userRepository(), $this->auth());
    }

    public function logoutAdmin(): LogoutAdminHandler
    {
        return new LogoutAdminHandler($this->auth());
    }

    public function getPublicSettings(): GetPublicSettingsHandler
    {
        return new GetPublicSettingsHandler($this->settingRepository());
    }

    public function listSettings(): ListSettingsHandler
    {
        return new ListSettingsHandler($this->settingRepository());
    }

    public function updateSetting(): UpdateSettingHandler
    {
        return new UpdateSettingHandler($this->settingRepository(), $this->activityLogRepository());
    }

    public function listBoxes(): ListBoxesHandler
    {
        return new ListBoxesHandler($this->boxRepository());
    }

    public function submitContact(): SubmitContactMessageHandler
    {
        return new SubmitContactMessageHandler($this->contactMessageRepository());
    }

    public function listContacts(): ListContactMessagesHandler
    {
        return new ListContactMessagesHandler($this->contactMessageRepository());
    }

    public function markContactRead(): MarkContactReadHandler
    {
        return new MarkContactReadHandler($this->contactMessageRepository());
    }

    public function listSubscriptions(): ListSubscriptionsHandler
    {
        return new ListSubscriptionsHandler($this->subscriptionRepository());
    }

    public function createBox(): CreateBoxHandler
    {
        return new CreateBoxHandler($this->boxRepository());
    }

    public function shipBox(): ShipBoxHandler
    {
        return new ShipBoxHandler($this->boxRepository());
    }

    public function ingestEvents(): IngestEventsHandler
    {
        return new IngestEventsHandler($this->analyticsEventRepository());
    }

    public function submitLead(): SubmitLeadHandler
    {
        return new SubmitLeadHandler($this->leadRepository());
    }

    public function listLeads(): ListLeadsHandler
    {
        return new ListLeadsHandler($this->leadRepository());
    }

    public function dashboardMetrics(): GetDashboardMetricsHandler
    {
        return new GetDashboardMetricsHandler(
            $this->analyticsEventRepository(),
            $this->leadRepository(),
            $this->pdo,
        );
    }

    public function listPublishedPosts(): ListPublishedPostsHandler
    {
        return new ListPublishedPostsHandler($this->postRepository());
    }

    public function getPublishedPost(): GetPublishedPostBySlugHandler
    {
        return new GetPublishedPostBySlugHandler($this->postRepository());
    }

    public function listAdminPosts(): ListAdminPostsHandler
    {
        return new ListAdminPostsHandler($this->postRepository());
    }

    public function getAdminPost(): GetAdminPostHandler
    {
        return new GetAdminPostHandler($this->postRepository());
    }

    public function savePost(): SavePostHandler
    {
        return new SavePostHandler($this->postRepository());
    }

    public function testimonialRepository(): PdoTestimonialRepository
    {
        return new PdoTestimonialRepository($this->pdo);
    }

    public function happyMediaRepository(): PdoHappyMediaRepository
    {
        return new PdoHappyMediaRepository($this->pdo);
    }

    public function faqItemRepository(): PdoFaqItemRepository
    {
        return new PdoFaqItemRepository($this->pdo);
    }

    public function homeContent(): HomeContentHandlers
    {
        return new HomeContentHandlers(
            $this->testimonialRepository(),
            $this->happyMediaRepository(),
            $this->faqItemRepository(),
        );
    }
}
