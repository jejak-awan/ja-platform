import { SystemModule } from './System/module';
import { SchoolModule } from './School/module';
import { CmsModule } from './Cms/module';
import { MediaModule } from './Media/module';
import { LibraryModule } from './Library/module';
import { LayoutModule } from './Layout/module';
import { FormsModule } from './Forms/module';
import { NewsletterModule } from './Newsletter/module';
import { SearchModule } from './Search/module';
import { InfraModule } from './Infra/module';

export const modules = [
    SystemModule,
    InfraModule,
    MediaModule,
    CmsModule,
    LibraryModule,
    LayoutModule,
    FormsModule,
    NewsletterModule,
    SearchModule,
    SchoolModule
];
