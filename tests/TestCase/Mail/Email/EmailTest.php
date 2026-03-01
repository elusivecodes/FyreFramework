<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Mail\Email;
use Fyre\Mail\Handlers\SendmailMailer;
use Fyre\Mail\MailManager;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class EmailTest extends TestCase
{
    use AttachmentTestTrait;
    use BccTestTrait;
    use BodyTestTrait;
    use BoundaryTestTrait;
    use CcTestTrait;
    use CharsetTestTrait;
    use FormatTestTrait;
    use FromTestTrait;
    use HeaderTestTrait;
    use PriorityTestTrait;
    use ReadReceiptTestTrait;
    use RecipientTestTrait;
    use ReplyToTestTrait;
    use ReturnPathTestTrait;
    use SenderTestTrait;
    use SubjectTestTrait;
    use ToTestTrait;

    protected Email $email;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Email::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Email::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->email = new Container()
            ->use(MailManager::class)
            ->build([
                'className' => SendmailMailer::class,
            ])
            ->email();
    }
}
