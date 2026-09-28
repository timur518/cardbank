<?php
// Системные инструкции и подтверждённые сведения загружаются отдельно.
function getSystemPrompt(): string
{
    return file_get_contents(__DIR__ . '/prompt.md') . "\n\nПОДТВЕРЖДЁННАЯ БАЗА ЗНАНИЙ:\n" . file_get_contents(__DIR__ . '/knowledge.md');
}
