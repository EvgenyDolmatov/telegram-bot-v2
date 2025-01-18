<?php

namespace App\Enums;

enum ThemeEnum: string
{
    case Education = 'education';
    case Games = 'games';
    case Movies = 'movies';
    case Sports = 'sports';
    case Music = 'music';
    case Tech = 'tech';
    case Science = 'science';
    case Health = 'health';
    case Food = 'food';
    case Travel = 'travel';
    case Art = 'art';
    case Fashion = 'fashion';
    case History = 'history';
    case Books = 'books';
    case Finance = 'finance';
    case Cars = 'cars';
    case Home = 'home';
    case Pets = 'pets';
    case News = 'news';
    case Fun = 'fun';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::Education,
            self::Games,
            self::Movies,
            self::Sports,
            self::Music,
            self::Tech,
            self::Science,
            self::Health,
            self::Food,
            self::Travel,
            self::Art,
            self::Fashion,
            self::History,
            self::Books,
            self::Finance,
            self::Cars,
            self::Home,
            self::Pets,
            self::News,
            self::Fun => StateEnum::PollRequestWaiting
        };
    }

    public function getCommand(): string
    {
        return '/' . $this->value;
    }

    public function getName(): string
    {
        return match ($this) {
            self::Education => 'Образование',
            self::Games => 'Игры',
            self::Movies => 'Кино',
            self::Sports => 'Спорт',
            self::Music => 'Музыка',
            self::Tech => 'Технологии',
            self::Science => 'Наука',
            self::Health => 'Здоровье',
            self::Food => 'Еда',
            self::Travel => 'Путешествия',
            self::Art => 'Искусство',
            self::Fashion => 'Мода',
            self::History => 'История',
            self::Books => 'Литература',
            self::Finance => 'Финансы',
            self::Cars => 'Автомобили',
            self::Home => 'Дом и сад',
            self::Pets => 'Животные',
            self::News => 'Новости',
            self::Fun => 'Развлечения',
        };
    }
}
