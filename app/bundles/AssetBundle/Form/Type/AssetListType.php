<?php

namespace Mautic\AssetBundle\Form\Type;

use Mautic\AssetBundle\Model\AssetModel;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetListType extends AbstractType
{
    public function __construct(private CorePermissions $corePermissions, private AssetModel $assetModel, private UserHelper $userHelper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices'           => $this->getAssetChoices(),
            'placeholder'       => false,
            'expanded'          => false,
            'multiple'          => true,
            'required'          => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    private function getAssetChoices(): array
    {
        $choices   = [];
        $viewOther = $this->corePermissions->isGranted('asset:assets:viewother');
        $repo      = $this->assetModel->getRepository();
        $repo->setCurrentUser($this->userHelper->getUser());
        $assets = $repo->getAssetList('', 0, 0, $viewOther);

        foreach ($assets as $asset) {
            $choices[$asset['language']][$asset['title']] = $asset['id'];
        }

        // sort by language
        ksort($choices);

        return $choices;
    }
}
