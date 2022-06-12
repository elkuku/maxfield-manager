<?php

namespace App\Controller\Admin;

use App\Entity\Maxfield;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MaxfieldCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Maxfield::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            AssociationField::new('owner'),
            TextareaField::new('jsonData')
                ->hideOnIndex(),
        ];
    }
}
