<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait SubjectTestTrait
{
    public function testHeaderSubject(): void
    {
        $this->email->setSubject('Test');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test',
            $headers['Subject']
        );
    }

    public function testHeaderSubjectCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setSubject('Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?=',
            $headers['Subject']
        );
    }

    public function testHeaderSubjectEncoding(): void
    {
        $this->email->setSubject('Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?=',
            $headers['Subject']
        );
    }

    public function testSetSubject(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setSubject('Test')
        );

        $this->assertSame(
            'Test',
            $this->email->getSubject()
        );
    }
}
