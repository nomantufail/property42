<?php

namespace App\Http\Controllers\Web;

use App\DB\Providers\SQL\Factories\Factories\Banners\BannersFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Requests\IndexRequest;
use App\Http\Requests\Requests\Property\GetPropertyRequest;
use App\Http\Requests\Requests\Property\RouteToAddPropertyRequest;
use App\Http\Requests\Requests\Property\SearchPropertiesRequest;
use App\Http\Requests\Requests\Property\UpdatePropertyRequest;
use App\Http\Responses\Responses\WebResponse;
use App\Libs\SearchEngines\Properties\Engines\Cheetah;
use App\Repositories\Providers\Providers\AssignedFeatureJsonRepoProvider;
use App\Repositories\Providers\Providers\BannersRepoProvider;
use App\Repositories\Providers\Providers\BlocksRepoProvider;
use App\Repositories\Providers\Providers\LandUnitsRepoProvider;
use App\Repositories\Providers\Providers\PropertiesJsonRepoProvider;
use App\Repositories\Providers\Providers\PropertiesRepoProvider;
use App\Repositories\Providers\Providers\PropertySubTypesRepoProvider;
use App\Repositories\Providers\Providers\PropertyTypesRepoProvider;
use App\Repositories\Providers\Providers\SocietiesRepoProvider;
use App\Repositories\Providers\Providers\UsersJsonRepoProvider;
use App\Repositories\Providers\Providers\UsersRepoProvider;
use App\Repositories\Repositories\Sql\FavouritePropertyRepository;
use App\Traits\Property\PropertyFilesReleaser;
use App\Traits\Property\PropertyPriceUnitHelper;
use App\Traits\Property\ShowAddPropertyFormHelper;
use App\Transformers\Response\PropertyTransformer;

class PropertiesController extends Controller
{
    use PropertyFilesReleaser, PropertyPriceUnitHelper, ShowAddPropertyFormHelper;
    public $PropertyTransformer = null;
    public $properties = "";
    public $societies = null;
    public $blocks = null;
    public $propertyTypes = null;
    public $propertySubtypes = null;
    public $landUnits = null;
    public $propertyStatuses = null;
    public $favouriteFactory = null;
    public $users = null;
    public $assignedFeaturesJson = null;
    public $propertiesRepo = null;
    public $userRepo = null;
    public $status = null;
    public $banners = null;

    public function __construct(WebResponse $webResponse, PropertyTransformer $propertyTransformer)
    {
        $this->response = $webResponse;
        $this->PropertyTransformer = $propertyTransformer;
        $this->propertiesRepo = (new PropertiesRepoProvider())->repo();
        $this->properties = (new PropertiesJsonRepoProvider())->repo();
        $this->societies = (new SocietiesRepoProvider())->repo();
        $this->blocks = (new BlocksRepoProvider())->repo();
        $this->propertyTypes = (new PropertyTypesRepoProvider())->repo();
        $this->propertySubtypes = (new PropertySubTypesRepoProvider())->repo();
        $this->landUnits = (new LandUnitsRepoProvider())->repo();
        $this->favouriteFactory = new FavouritePropertyRepository();
        $this->users = (new UsersJsonRepoProvider())->repo();
        $this->userRepo = (new UsersRepoProvider())->repo();
        $this->assignedFeaturesJson = (new AssignedFeatureJsonRepoProvider())->repo();
        $this->status = new \PropertyStatusTableSeeder();
        $this->banners = (new BannersRepoProvider())->repo();
    }
    public function addProperty(RouteToAddPropertyRequest $request)
    {
        if($request->isNotAuthentic()){
            die(header('Location: '.url('/').'/app/add-property#/'));
        }else{
            die(header('Location: '.url('/').'/dashboard#/home/properties/add'));
        }
    }

    public function update(UpdatePropertyRequest $request)
    {
        return $this->response
            ->setView('userRegistered')
            ->respond($this->PropertyTransformer->transform($request->all()));
    }

    /**
     * @param SearchPropertiesRequest $request
     * @return $this
     */
    public function search(SearchPropertiesRequest $request)
    {
        $params = $request->getParams();
        $params['sortBy'] = 'updated_at';
        $properties = $this->properties->search($request->getParams());
        $propertiesCount = count($properties);
        $totalPropertiesFound = (new Cheetah())->count();
        $banners = $this->getPropertyListingPageBanners($params);
        return $this->response->setView('frontend.v2.property_listing')->respond(['data' => [
            'properties' => $this->releaseAllPropertiesFiles($properties),
            'totalProperties'=> $totalPropertiesFound[0]->total_records,
            'societies'=>$this->societies->all(),
            'blocks'=>$this->blocks->getBlocksBySociety($request->get('societyId')),
            'propertyTypes'=>$this->propertyTypes->all(),
            'propertySubtypes'=>$this->propertySubtypes->getByType($request->get('propertyTypeId')),
            'landUnits'=>$this->landUnits->all(),
            'propertiesCount'=>$propertiesCount,
            'oldValues'=>$request->all(),
            'banners'=>$banners
        ]]);
    }
    public function getPropertyListingPageBanners($params)
    {
        $leftBanners = $this->banners->getBanners([
            'bannerType'=>'relevant',
            'position'=>'left',
            'page'=>'property_listing',
            'landUnitId'=>$params['landUnitId'],
            'societyId'=>$params['societyId'],
            'landAreaFrom'=>$params['landAreaFrom'],
            'landAreaTo'=>$params['landAreaTo']
        ]);
        $topBanners  = $this->banners->getBanners([
            'bannerType'=>'fix',
            'position'=>'top',
            'page'=>'property_listing',
            'societyId'=>$params['societyId'],
            'landAreaFrom'=>$params['landAreaFrom'],
            'landAreaTo'=>$params['landAreaTo']
        ]);
        $rightBanners  = $this->banners->getBanners([
            'bannerType'=>'fix',
            'position'=>'right',
            'page'=>'property_listing',

        ]);
        $betweenBanners  = $this->banners->getBanners([
            'bannerType'=>'relevant',
            'position'=>'between',
            'page'=>'property_listing',
            'societyId'=>$params['societyId'],
            'landAreaFrom'=>$params['landAreaFrom'],
            'landAreaTo'=>$params['landAreaTo']
        ]);
        return $banners = [
            'leftBanners'=>$leftBanners,
            'topBanners'=>$topBanners,
            'rightBanners'=>$rightBanners,
            'between'=>$betweenBanners
        ];
    }
    public function getIndexPageBanners()
    {
        $leftBanners = $this->banners->getBanners(['bannerType'=>'fix','position'=>'left','page'=>'index']);
        $topBanners  = $this->banners->getBanners(['bannerType'=>'fix','position'=>'top','page'=>'index']);
        $rightBanners  = $this->banners->getBanners(['bannerType'=>'fix','position'=>'right','page'=>'index']);
        return $banners = [
            'leftBanners'=>$leftBanners,
            'topBanners'=>$topBanners,
            'rightBanners'=>$rightBanners
        ];
    }
    public function index(IndexRequest $request)
    {
        $agents = $this->users->getTrustedAgentsWithPriority(['limit'=>36]);
        $importantSocieties = $this->societies->getImportantSocieties();
        $banners = $this->getIndexPageBanners();
        $saleAndRentCount = $this->propertiesRepo->countSaleAndRendProperties();
        return $this->response->setView('frontend.v2.index')->respond(['data' => [
            'societies'=>$this->societies->all(),
            'propertyTypes'=>$this->propertyTypes->all(),
            'propertySubtypes'=>$this->propertySubtypes->all(),
            'landUnits'=>$this->landUnits->all(),
            'agents'=>$this->releaseUsersAgenciesLogo($agents),
            'importantSocieties'=>$importantSocieties,
            'saleAndRentCount'=>$saleAndRentCount,
            'banners'=>$banners
        ]]);
    }
    public function getById(GetPropertyRequest $request)
    {
       try {
           $property = $this->properties->getById($request->get('propertyId'));
           if($property->propertyStatus->id == ($this->status->getActiveStatusId()))
           {
               $this->propertiesRepo->IncrementViews($request->get('propertyId'));
               $loggedInUser = $request->user();
               $property = $this->convertPropertyAreaToActualUnit($property);
               $propertyOwner = $this->users->find($property->owner->id);
               return $this->response->setView('frontend.v2.property_detail')->respond(['data' => [
                   'isFavourite' => ($loggedInUser == null) ? false : $this->favouriteFactory->isFavourite($request->get('propertyId'), $loggedInUser->id),
                   'property' => $this->releaseAllPropertiesFiles([$property])[0],
                   'loggedInUser' => $loggedInUser,
                   'user' => $this->users->find($property->owner->id),
                   'propertyOwner' => $propertyOwner,
                   'banners'=>$this->getPropertyDetailPageBanners(),
                   'propertyId' => $request->get('propertyId')
               ]]);
           }else {
               return $this->response->setView('frontend.v2.No-result')->respond(['data' => [
                   'propertyId' => $request->get('propertyId')
               ]]);
           }
       }
       catch(\Exception $e){
           return $this->response->setView('frontend.v2.No-result')->respond(['data' => [
               'propertyId' => $request->get('propertyId')
           ]]);
       }
    }

        public function getPropertyDetailPageBanners()
        {
            $leftBanners = $this->banners->getBanners([
                'bannerType'=>'fix',
                'position'=>'left',
                'page'=>'property_detail'

            ]);

            $topBanners  = $this->banners->getBanners([
                'bannerType'=>'fix',
                'position'=>'top',
                'page'=>'property_detail'
            ]);
            $rightBanners  = $this->banners->getBanners([
                'bannerType'=>'fix',
                'position'=>'right',
                'page'=>'property_detail'
            ]);
            return $banners = [
                'leftBanners'=>$leftBanners,
                'topBanners'=>$topBanners,
                'rightBanners'=>$rightBanners,
            ];
        }
}
