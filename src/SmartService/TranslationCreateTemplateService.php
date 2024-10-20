<?php

namespace Partitech\SonataExtra\SmartService;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Service\CodeExtractor;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;


class TranslationCreateTemplateService
{
    private ParameterBagInterface $params;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private $projectDir;
    private LoggerInterface $logger;
    private ContainerInterface $container;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        ContainerInterface    $container,
        LoggerInterface       $logger,
        Filesystem $filesystem
    ): void
    {
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->container = $container;
        $this->filesystem = $filesystem;
    }

    public function setProjectDir($projectDir){
        $this->projectDir=$projectDir;
    }

    public function getClassNameByObject($object){
        $className = str_replace('\\', '_', get_class($object));
        return $className;
    }
    public function getClassNameByEntityClass($entity){
        $className = str_replace('\\', '_', $entity);
        return $className;
    }
    public function createDirectory($projectDir=false){

        if(!empty($projectDir)){
            $this->projectDir=$projectDir;
        }else{
            $this->logger->critical("projectDir can not be empty in TranslationCreateTemplateService::createDirectory");
            throw new InvalidArgumentException("projectDir can not be empty in TranslationCreateTemplateService::createDirectory");

        }

        if ($this->filesystem->exists($projectDir)) {

            $this->filesystem->remove($projectDir);
        }

        $this->logger->critical("projectDir : ".$projectDir);
        $this->filesystem->mkdir($projectDir);
        if (!$this->filesystem->exists($projectDir)) {
            $this->logger->critical("projectDir can not be created in TranslationCreateTemplateService::createDirectory");
            throw new InvalidArgumentException("ProjectDir can not be created in TranslationCreateTemplateService::createDirectory");
        }


    }

    public function createPayloadArray($payLoad,$translateArray){
        if(empty($this->projectDir)){
            $this->logger->critical("projectDir can not be empty in TranslationCreateTemplateService::createDirectory");
            throw new InvalidArgumentException("projectDir can not be empty in TranslationCreateTemplateService::createDirectory");
        }

        $filePath = $this->projectDir . '/payloadArray.txt';
        $payLoadString = is_array($payLoad) ? json_encode($payLoad) : $payLoad;
        file_put_contents($filePath, $payLoadString);
        $filePath = $this->projectDir . '/payloadArray_translated.json';
        $translateArrayString = json_encode($translateArray,JSON_PRETTY_PRINT);
        file_put_contents($filePath, $translateArrayString);
    }

    public function createPayloadHtmlField($field, $payLoad,$placeholders){
        if(empty($this->projectDir)){
            $this->logger->critical("projectDir can not be empty in TranslationCreateTemplateService::createDirectory");
            throw new InvalidArgumentException("projectDir can not be empty in TranslationCreateTemplateService::createDirectory");

        }

        $file_name_payload='field_'.$field.'_payload.txt';
        $file_name_translation='field_'.$field.'_translation.txt';
        $file_name_code_blocks='field_'.$field.'_code_blocks.json';

        $filePath = $this->projectDir . '/'.$file_name_payload;
        $payLoadString = is_array($payLoad) ? json_encode($payLoad) : $payLoad;
        file_put_contents($filePath, $payLoadString);

        $filePath = $this->projectDir . '/'.$file_name_code_blocks;
        $code_blocks = json_encode($placeholders,JSON_PRETTY_PRINT);
        file_put_contents($filePath, $code_blocks);

        $filePath = $this->projectDir . '/'.$file_name_payload;
        file_put_contents($filePath,$payLoad);

        $filePath = $this->projectDir . '/'.$file_name_translation;
        file_put_contents($filePath,'Place translated content here. be sure to have the initial strucure and placeholders.');

    }

    public function getTemplateTranslationPath($serviceName)
    {
        $directoryPath = sprintf('%s/var/translation/%s', $this->container->getParameter('kernel.project_dir'), $serviceName);
        return $directoryPath;
    }

    public function readTemplateTranslationPath($directoryPath)
    {
        $translations=[];
        if ($this->filesystem->exists($directoryPath)) {
            $directory = opendir($directoryPath);
             while (($item = readdir($directory)) !== false) {
                 if ($item != "." && $item != "..") {
                     $translations[]=$item;
                 }
             }

        } else {
            $this->logger->critical("directory ".$directoryPath." does not exist in  TranslationCreateTemplateService::readTemplateTranslationPath");
            throw new InvalidArgumentException("directory ".$directoryPath." does not exist in  TranslationCreateTemplateService::readTemplateTranslationPath");

        }
        return $translations;
    }
    public function importTranslation(
        $translationPath,
        $itemId,
        $entityClass)
    {

        $object_id=explode('_',$itemId);
        $object_id_src=$object_id[0];
        $target_translation=$object_id[1];
        $repository=$this->entityManager->getRepository($entityClass);
        $object_src = $repository->createQueryBuilder('e')
            ->andWhere('e.id = :id')
            ->setParameter('id', $object_id_src)
            ->getQuery()
            ->getOneOrNullResult();

dump($object_id_src);


        if(!empty($object_src)){

            $target_object_id=$object_src->translations[$target_translation]['entity_id'];
            $object = $repository->createQueryBuilder('e')
                ->andWhere('e.id = :id')
                ->setParameter('id', $target_object_id)
                ->getQuery()
                ->getOneOrNullResult();

            $translationDir=$translationPath.'/'.$itemId;
            $translationArray=json_decode(file_get_contents($translationDir.'/payloadArray_translated.json'));
            foreach($translationArray as $field=>$value){
                $setMethodName = "set" . ucfirst($field);
                if($value=='file'){
                    $CodeExtractor=new CodeExtractor;
                    $codeBlocks = json_decode(file_get_contents($translationDir.'/field_'.$field.'_code_blocks.json'), true);

                    $CodeExtractor->setCodeBlocks($codeBlocks);

                    $translatedMarkup =file_get_contents($translationDir.'/field_'.$field.'_translation.txt');
                    $htmlMarkup=$CodeExtractor->replaceCodeBlocks($translatedMarkup);

                    $object->$setMethodName($htmlMarkup);
                }else{
                    /*dump($itemId);
                    dump($setMethodName);
                    dump($value);*/

                    $object->$setMethodName($value);
                }

            }
            $this->entityManager->persist($object);
            $this->entityManager->flush();
        }


    }

}